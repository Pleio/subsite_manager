--- add site guid column to metadata tabel
ALTER TABLE prefix_metadata DISABLE KEYS;
	ALTER TABLE prefix_metadata ADD site_guid BIGINT UNSIGNED AFTER owner_guid;
	ALTER TABLE prefix_metadata MODIFY site_guid BIGINT UNSIGNED DEFAULT 0;
	ALTER TABLE prefix_metadata ADD KEY site_guid (site_guid);
ALTER TABLE prefix_metadata ENABLE KEYS;

--- add site guid column to annotations tabel
ALTER TABLE prefix_annotations DISABLE KEYS;
	ALTER TABLE prefix_annotations ADD site_guid BIGINT UNSIGNED AFTER owner_guid;
	ALTER TABLE prefix_annotations MODIFY site_guid BIGINT UNSIGNED DEFAULT 0;
	ALTER TABLE prefix_annotations ADD KEY site_guid (site_guid);
ALTER TABLE prefix_annotations ENABLE KEYS;

--- add site guid column to river tabel
ALTER TABLE prefix_river DISABLE KEYS;
	ALTER TABLE prefix_river ADD site_guid BIGINT UNSIGNED AFTER action_type;
	ALTER TABLE prefix_river MODIFY site_guid BIGINT UNSIGNED DEFAULT 0;
	ALTER TABLE prefix_river ADD KEY site_guid (site_guid);
ALTER TABLE prefix_river ENABLE KEYS;

--- add site guid column to system_log tabel
ALTER TABLE prefix_system_log DISABLE KEYS;
	ALTER TABLE prefix_system_log ADD site_guid BIGINT UNSIGNED AFTER owner_guid;
	ALTER TABLE prefix_system_log MODIFY site_guid BIGINT UNSIGNED DEFAULT 0;
	ALTER TABLE prefix_system_log ADD KEY site_guid (site_guid);
ALTER TABLE prefix_system_log ENABLE KEYS;