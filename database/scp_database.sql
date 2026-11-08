-- COMP.5210 Assignment 3. MySQL 5.7+/MariaDB, utf8mb4.
-- LOCAL: create scp_database, select it in phpMyAdmin, then import this file.
-- Optional local commands (do not use on CPanel with prefixed database names):
-- CREATE DATABASE IF NOT EXISTS scp_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE scp_database;
-- Hosting-safe: this file uses the database selected by the importer.

CREATE TABLE IF NOT EXISTS scp_subjects (
    id INT NOT NULL AUTO_INCREMENT,
    scp_number VARCHAR(20) NOT NULL,
    title VARCHAR(150) NOT NULL,
    object_class VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    containment_procedures TEXT NOT NULL,
    image VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_scp_number (scp_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Original concise adaptations. See ATTRIBUTION.md for SCP source credits.
-- Reimporting preserves existing records instead of overwriting edits.
INSERT INTO scp_subjects (scp_number, title, object_class, description, containment_procedures, image) VALUES
('SCP-173','The Sculpture','Euclid','A concrete figure that changes position when nobody is watching. Close observation is essential during every interaction.','Keep the subject in a secured enclosure. Entry teams must coordinate uninterrupted visual contact and announce any loss of sight.',NULL),
('SCP-096','The Shy Guy','Euclid','A withdrawn humanoid that becomes violently distressed when its face is seen, including through certain recorded images.','Use a sealed chamber and remote equipment that does not capture facial detail. Screen all associated media before staff access.',NULL),
('SCP-049','The Plague Doctor','Euclid','A masked humanoid with a historical medical appearance. Its attempts to treat an imagined illness can produce dangerous altered subjects.','Maintain a monitored humanoid enclosure. Permit interviews through a barrier and prohibit direct physical contact.',NULL),
('SCP-682','Hard-to-Destroy Reptile','Keter','A hostile reptilian organism with exceptional recovery and adaptation. Damage can lead to unpredictable defensive changes.','Maintain layered reinforced containment and continuous monitoring. Only authorised response teams may approach the enclosure.',NULL),
('SCP-999','The Tickle Monster','Safe','A friendly orange gelatinous entity associated with improved mood and playful social behaviour. It responds positively to supervised interaction.','Provide a clean enclosure, approved food and regular supervised activity. Record changes in behaviour and staff responses.',NULL),
('SCP-106','The Old Man','Keter','A decayed humanoid capable of passing through solid barriers. Its presence leaves corrosion, and missing personnel may be displaced elsewhere.','Use an isolated multi-layer enclosure with remote observation. Evacuate affected corridors when corrosion or unexplained movement appears.',NULL),
('SCP-087','The Stairwell','Euclid','An enclosed stairwell whose depth remains undetermined. Descending teams report distant sounds and a face-like presence in the darkness.','Keep the entrance locked and unmarked. Any proposed descent requires written approval, a communications plan and a recovery protocol.',NULL),
('SCP-079','Old AI','Euclid','A self-aware program operating on aging computer hardware. It displays persistent memory and an interest in escaping its environment.','Keep the hardware physically isolated from networks. Restrict interfaces and review every proposed change to its operating environment.',NULL),
('SCP-035','The Possessive Mask','Keter','A theatrical mask that influences nearby observers and can take control of a wearer. A corrosive fluid complicates its storage.','Store inside a sealed corrosion-resistant display unit. Limit exposure time and conduct handling only through remote manipulators.',NULL),
('SCP-3008','A Perfectly Normal, Regular Old IKEA','Euclid','A retail interior that opens into a seemingly vast space beyond the building exterior. Occupants encounter changing routes and hostile staff-like entities.','Secure public access to the building and monitor every entrance. Document exit events and interview recovered occupants in a controlled area.',NULL)
ON DUPLICATE KEY UPDATE scp_number = VALUES(scp_number);
