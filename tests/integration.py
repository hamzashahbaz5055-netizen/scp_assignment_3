"""Isolated CRUD integration checks; Python is for development only, not hosting.

Usage: python tests/integration.py --php PATH_TO_PHP --mysqld PATH_TO_MYSQLD
Creates a temporary MySQL instance on port 13316 and PHP server on port 18080.
It does not connect to or change the configured application database.
"""
import argparse
import http.cookiejar
import os
from pathlib import Path
import re
import socket
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--php', required=True)
    parser.add_argument('--mysqld', required=True)
    parser.add_argument('--node', help='Optional Node executable to run browser.cjs; Playwright must be available via NODE_PATH')
    args = parser.parse_args()
    root = Path(__file__).resolve().parents[1]
    php = str(Path(args.php).resolve())
    mysql = str(Path(args.mysqld).resolve())
    mysqladmin = str(Path(mysql).with_name('mysqladmin.exe' if os.name == 'nt' else 'mysqladmin'))
    # Refuse occupied ports: never attach tests to an existing database/server.
    for port in (13316, 18080):
        with socket.socket() as connection:
            if connection.connect_ex(('127.0.0.1', port)) == 0:
                raise RuntimeError(f'Test port {port} is occupied. Stop the test service first.')
    processes = []
    with tempfile.TemporaryDirectory(prefix='scp-crud-check-') as directory:
        temp = Path(directory)
        log = (temp / 'runtime.log').open('w', encoding='utf-8')
        try:
            def run(command, **kwargs):
                return subprocess.run(command, check=True, capture_output=True, text=True, **kwargs)
            def wait_port(port):
                for _ in range(150):
                    with socket.socket() as connection:
                        if connection.connect_ex(('127.0.0.1', port)) == 0:
                            return
                    time.sleep(.2)
                raise RuntimeError(f'Service on port {port} did not start')
            for file in root.rglob('*.php'):
                run([php, '-l', str(file)])
            print('PASS: all PHP files pass syntax checks', flush=True)
            run([mysql, '--no-defaults', '--initialize-insecure', f'--datadir={temp / "data"}'], timeout=90)
            processes.append(subprocess.Popen([mysql, '--no-defaults', f'--datadir={temp / "data"}', '--port=13316', '--bind-address=127.0.0.1', '--mysqlx=0', '--skip-log-bin'], stdout=log, stderr=log))
            wait_port(13316)
            php_options = [php, '-d', f'extension_dir={Path(php).parent / "ext"}', '-d', 'extension=pdo_mysql', '-d', f'session.save_path={temp}', '-d', f'error_log={temp / "php.log"}']
            bootstrap = temp / 'bootstrap.php'
            bootstrap.write_text("<?php\n$p = new PDO('mysql:host=127.0.0.1;port=13316;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);\n$p->exec('CREATE DATABASE scp_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');\n$p->exec('USE scp_database');\n$p->exec(file_get_contents($argv[1]));\n$p->exec(file_get_contents($argv[1]));\necho $p->query('SELECT COUNT(*) FROM scp_subjects')->fetchColumn();", encoding='utf-8')
            assert run(php_options + [str(bootstrap), str(root / 'database/scp_database.sql')]).stdout == '10'
            print('PASS: SQL imports twice with exactly ten unique seed records', flush=True)
            env = dict(os.environ, SCP_DB_HOST='127.0.0.1;port=13316', SCP_DB_NAME='scp_database', SCP_DB_USER='root', SCP_DB_PASSWORD='')
            processes.append(subprocess.Popen(php_options + ['-S', '127.0.0.1:18080', '-t', str(root.parent)], env=env, stdout=log, stderr=log))
            wait_port(18080)
            base = 'http://127.0.0.1:18080/scp-database/'
            opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
            def request(path, data=None):
                payload = urllib.parse.urlencode(data).encode() if data is not None else None
                try:
                    with opener.open(base + path, payload, timeout=10) as response:
                        return response.status, response.read().decode('utf-8')
                except urllib.error.HTTPError as error:
                    return error.code, error.read().decode('utf-8')
            def token(html):
                return re.search(r'name="csrf" value="([a-f0-9]+)"', html).group(1)
            def expect(name, condition):
                assert condition, name
                print('PASS: ' + name, flush=True)
            status, listing = request('index.php')
            expect('database-backed list and ten cards in a subfolder', status == 200 and listing.count('<article class="scp-card') == 10)
            for query, expected in [('SCP-096', 1), ('Tickle', 1), ('Euclid', 6), ('no-such-anomaly', 0), ("' OR 1=1 --", 0), ('%', 0), ('_', 0)]:
                status, body = request('index.php?q=' + urllib.parse.quote(query))
                expect('literal safe search: ' + query, status == 200 and body.count('<article class="scp-card') == expected)
            for endpoint in ('view.php', 'edit.php', 'delete.php'):
                for suffix in ('', '?id=abc', '?id=0', '?id=-1', '?id[]=1', '?id=99999999'):
                    status, body = request(endpoint + suffix)
                    assert status == 404 and 'Record not found' in body
            print('PASS: missing, malformed, array and nonexistent IDs on view/edit/delete', flush=True)
            _, form = request('create.php')
            csrf = token(form)
            status, body = request('create.php', {'csrf': csrf, 'title': 'Retained title'})
            expect('required validation and retained form input', 'SCP number is required.' in body and 'value="Retained title"' in body)
            data = dict(csrf=csrf, scp_number='SCP-900001', title='<script>alert(1)</script>', object_class='Safe', description='Integration test description', containment_procedures='Test containment', image='')
            _, body = request('create.php', dict(data, csrf='invalid'))
            expect('create rejects invalid CSRF', 'token is invalid or expired' in body)
            _, body = request('create.php', dict(data, scp_number='SCP-173'))
            expect('duplicate seed number rejected', 'This SCP number already exists.' in body)
            _, body = request('create.php', dict(data, object_class='Invalid'))
            expect('invalid class rejected', 'Select a valid object class.' in body)
            _, body = request('create.php', dict(data, image='javascript:alert(1)'))
            expect('unsafe image URL rejected', 'Enter an HTTPS image URL' in body)
            _, body = request('create.php', dict(data, title='x' * 151))
            expect('server enforces title length', 'Use no more than 150 characters.' in body)
            _, body = request('create.php', data)
            expect('create, redirect and escaped XSS text', 'SCP-900001 was created successfully.' in body and '&lt;script&gt;' in body and '<script>alert(1)</script>' not in body)
            record_id = re.search(r'data-delete-id="(\d+)" data-delete-number="SCP-900001"', body).group(1)
            _, body = request('index.php')
            expect('flash consumed and no refresh insertion', 'was created successfully' not in body and body.count('<article class="scp-card') == 11)
            _, body = request('view.php?id=' + record_id)
            expect('view saved text, timestamps and local placeholder', 'Integration test description' in body and 'Last updated' in body and 'placeholder-scp.svg' in body and '<script>alert(1)</script>' not in body)
            _, body = request('edit.php?id=' + record_id)
            expect('edit prepopulates fields with escaped output', 'value="SCP-900001"' in body and '&lt;script&gt;' in body)
            _, body = request('edit.php?id=' + record_id, dict(data, csrf='bad'))
            expect('edit rejects invalid CSRF', 'token is invalid or expired' in body)
            _, body = request('edit.php?id=' + record_id, dict(data, scp_number='SCP-173'))
            expect('edit rejects another record number', 'This SCP number already exists.' in body)
            data.update(title='Edited test record', object_class='Thaumiel')
            _, body = request('edit.php?id=' + record_id, data)
            expect('update saves and permits its own number', 'SCP-900001 was updated successfully.' in body)
            _, body = request('view.php?id=' + record_id)
            expect('updated values persist', 'Edited test record' in body and 'Thaumiel' in body)
            _, body = request('delete.php?id=' + record_id)
            expect('GET deletion only displays confirmation', 'This action cannot be undone.' in body and 'Cancel' in body)
            status, _ = request('view.php?id=' + record_id)
            expect('cancel leaves record intact', status == 200)
            status, _ = request('delete.php', {'id': record_id, 'csrf': 'bad'})
            expect('delete rejects invalid CSRF', status == 403)
            _, body = request('delete.php', {'id': record_id, 'csrf': csrf})
            expect('POST deletes and flashes success', 'SCP record deleted successfully.' in body and body.count('<article class="scp-card') == 10)
            status, _ = request('view.php?id=' + record_id)
            expect('deleted record is no longer readable', status == 404)
            _, body = request('delete.php', {'id': record_id, 'csrf': csrf})
            expect('stale deletion handled', 'Record not found. It may already have been deleted.' in body)
            status, body = request('index.php?q[]=bad')
            expect('array search parameter handled safely', status == 200 and body.count('<article class="scp-card') == 10)
            for file in ('about.php', 'assets/css/style.css', 'assets/js/script.js', 'assets/images/placeholder-scp.svg'):
                status, _ = request(file)
                assert status == 200, file
            print('PASS: About page and bundled assets available', flush=True)
            if args.node:
                result = subprocess.run([args.node, str(root / 'tests/browser.cjs'), base], capture_output=True, text=True, timeout=180)
                print(result.stdout, result.stderr, flush=True)
                result.check_returncode()
            # On Windows mysqld may supervise a child process. Use protocol shutdown.
            run([mysqladmin, '--no-defaults', '--protocol=TCP', '--host=127.0.0.1', '--port=13316', '--user=root', 'shutdown'], timeout=40)
            processes[0].wait(timeout=30)
            status, body = request('index.php')
            expect('database outage is friendly without raw exceptions', status == 503 and 'Archive temporarily unavailable' in body and 'SQLSTATE' not in body)
            print('ALL INTEGRATION CHECKS PASSED', flush=True)
        finally:
            if processes:
                subprocess.run([mysqladmin, '--no-defaults', '--protocol=TCP', '--host=127.0.0.1', '--port=13316', '--user=root', 'shutdown'], capture_output=True, timeout=40)
                # Let the Windows supervisor reap its child after graceful shutdown.
                try:
                    processes[0].wait(timeout=40)
                except subprocess.TimeoutExpired:
                    pass
            for process in reversed(processes):
                if process.poll() is None:
                    process.terminate()
                    try:
                        process.wait(timeout=30)
                    except subprocess.TimeoutExpired:
                        process.kill()
                        process.wait()
            log.close()
            php_log = temp / 'php.log'
            if php_log.exists():
                content = php_log.read_text(encoding='utf-8', errors='replace')
                unexpected = [line for line in content.splitlines() if '[SCP database] SQLSTATE[HY000] [2002]' not in line]
                if unexpected:
                    print('PHP diagnostics:', '\n'.join(unexpected))


if __name__ == '__main__':
    main()
