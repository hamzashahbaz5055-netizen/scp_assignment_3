// Optional development check. Hosting does not require Node or Playwright.
const { chromium } = require('playwright');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');

(async () => {
  const base = process.argv[2];
  const output = path.join(__dirname, 'evidence');
  fs.mkdirSync(output, { recursive: true });
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  try {
    const page = await browser.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    for (const [width, height] of [[1920,1080],[1366,768],[1024,768],[768,1024],[390,844],[375,667]]) {
      await page.setViewportSize({ width, height });
      await page.goto(base, { waitUntil: 'networkidle' });
      assert.equal(await page.locator('.scp-card').count(), 10);
      const cards = await page.locator('.scp-card').evaluateAll(elements => elements.map(element => { const box = element.getBoundingClientRect(); return { x: box.x, y: box.y }; }));
      const columns = cards.filter(card => Math.abs(card.y - cards[0].y) < 2).length;
      assert.equal(columns, width >= 992 ? 3 : width >= 768 ? 2 : 1, `Grid at ${width}`);
      for (const endpoint of ['', 'create.php', 'view.php?id=1', 'edit.php?id=1', 'delete.php?id=1', 'about.php']) {
        await page.goto(base + endpoint, { waitUntil: 'networkidle' });
        assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `Overflow on ${endpoint} at ${width}`);
      }
      await page.goto(base, { waitUntil: 'networkidle' });
      if (width < 768) {
        await page.getByRole('button', { name: 'Toggle navigation' }).click();
        await page.waitForFunction(() => document.querySelector('#navigation').classList.contains('show'));
        assert(await page.getByRole('link', { name: 'About', exact: true }).isVisible());
        await page.getByRole('button', { name: 'Toggle navigation' }).click();
      }
      await page.screenshot({ path: path.join(output, `database-${width}.png`), fullPage: true });
      console.log(`PASS: ${width}×${height}, correct columns, no overflow on six pages${width < 768 ? ', mobile menu works' : ''}`);
    }
    await page.setViewportSize({ width: 1366, height: 768 });
    await page.goto(base, { waitUntil: 'networkidle' });
    await page.getByRole('link', { name: 'Delete SCP-173', exact: true }).click();
    await page.getByRole('dialog').waitFor({ state: 'visible' });
    await page.locator('#deleteModal').evaluate(element => new Promise(resolve => {
      if (getComputedStyle(element).opacity === '1' && getComputedStyle(element.querySelector('.modal-dialog')).transform === 'none') resolve();
      else element.addEventListener('shown.bs.modal', resolve, { once: true });
    }));
    assert.equal(await page.locator('#deleteRecordId').inputValue(), '1');
    await page.screenshot({ path: path.join(output, 'delete-modal.png') });
    await page.getByRole('button', { name: 'Cancel', exact: true }).click();
    await page.getByRole('dialog').waitFor({ state: 'hidden' });
    assert.equal(await page.locator('.scp-card').count(), 10);
    console.log('PASS: Bootstrap delete modal opens, targets selected ID and cancels');
    await page.getByRole('searchbox').fill('SCP-096');
    await page.getByRole('button', { name: 'Search', exact: true }).click();
    await page.waitForURL('**/index.php?q=SCP-096');
    assert.equal(await page.locator('.scp-card').count(), 1);
    console.log('PASS: browser search submits and filters records');
    await page.goto(base + 'create.php', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(output, 'create-desktop.png'), fullPage: true });
    await page.getByRole('button', { name: 'Create SCP record', exact: true }).click();
    await page.getByRole('alert').waitFor();
    assert(await page.locator('#scp_number').getAttribute('aria-invalid') === 'true');
    console.log('PASS: browser form submission displays accessible server validation');
    await page.goto(base + 'view.php?id=1', { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(output, 'view-desktop.png'), fullPage: true });
    assert.deepEqual(errors, [], 'No browser JavaScript errors');
    console.log('PASS: no browser JavaScript errors; screenshots saved in tests/evidence');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
