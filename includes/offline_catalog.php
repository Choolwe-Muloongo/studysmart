<?php

function ensureOfflineCatalogTable(Database $db): void {
    $db->execute("CREATE TABLE IF NOT EXISTS student_offline_catalog (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        resource_id INT NOT NULL,
        resource_type ENUM('video','document','music') NOT NULL,
        title VARCHAR(255) NOT NULL,
        course_id INT DEFAULT NULL,
        local_key VARCHAR(255) DEFAULT NULL,
        cache_key VARCHAR(255) DEFAULT NULL,
        downloaded_at DATETIME DEFAULT NULL,
        size_bytes BIGINT DEFAULT 0,
        status ENUM('downloaded','pending','failed','removed','online_only') NOT NULL DEFAULT 'downloaded',
        requires_network TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_user_resource_type (user_id, resource_id, resource_type),
        KEY idx_user_type_status (user_id, resource_type, status),
        KEY idx_course_id (course_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function offlineCacheKey(int $resourceId, string $resourceType): string {
    if ($resourceType === 'video') {
        return "../includes/video_stream.php?id={$resourceId}";
    }
    return "../includes/document_stream.php?id={$resourceId}";
}

function offlineOnlineUrl(int $resourceId, string $resourceType): string {
    if ($resourceType === 'video') {
        return "videos.php?watch={$resourceId}";
    }
    if ($resourceType === 'music') {
        return "music.php";
    }
    return "resources.php?view={$resourceId}";
}

function offlineUpsertDownload(Database $db, int $userId, array $resource, string $resourceType, ?string $status = null): void {
    $resourceId = (int)$resource['id'];
    $cacheKey = offlineCacheKey($resourceId, $resourceType);
    $requiresNetwork = (!empty($resource['external_url']) && empty($resource['file_path'])) ? 1 : 0;
    $status = $status ?? ($requiresNetwork ? 'online_only' : 'downloaded');

    $db->execute(
        "INSERT INTO student_offline_catalog
            (user_id, resource_id, resource_type, title, course_id, local_key, cache_key, downloaded_at, size_bytes, status, requires_network)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            course_id = VALUES(course_id),
            local_key = VALUES(local_key),
            cache_key = VALUES(cache_key),
            downloaded_at = VALUES(downloaded_at),
            size_bytes = VALUES(size_bytes),
            status = VALUES(status),
            requires_network = VALUES(requires_network)",
        [
            $userId,
            $resourceId,
            $resourceType,
            (string)($resource['title'] ?? 'Untitled'),
            isset($resource['course_id']) ? (int)$resource['course_id'] : null,
            $cacheKey,
            $cacheKey,
            (int)($resource['file_size'] ?? 0),
            $status,
            $requiresNetwork
        ]
    );
}

function offlineSetStatus(Database $db, int $userId, int $resourceId, string $resourceType, string $status): void {
    $downloadedAt = $status === 'downloaded' ? 'NOW()' : 'NULL';
    $db->execute(
        "UPDATE student_offline_catalog
         SET status = ?, downloaded_at = {$downloadedAt}, updated_at = CURRENT_TIMESTAMP
         WHERE user_id = ? AND resource_id = ? AND resource_type = ?",
        [$status, $userId, $resourceId, $resourceType]
    );
}

function offlineRemoveDownload(Database $db, int $userId, int $resourceId, string $resourceType): void {
    $db->execute(
        "UPDATE student_offline_catalog
         SET status = 'removed', updated_at = CURRENT_TIMESTAMP
         WHERE user_id = ? AND resource_id = ? AND resource_type = ?",
        [$userId, $resourceId, $resourceType]
    );
}

function offlineStatusMap(Database $db, int $userId, array $resourceIds, string $resourceType): array {
    if (empty($resourceIds)) {
        return [];
    }
    $placeholders = implode(',', array_fill(0, count($resourceIds), '?'));
    $params = array_merge([$userId, $resourceType], $resourceIds);
    $rows = $db->fetchAll(
        "SELECT resource_id, status, requires_network
         FROM student_offline_catalog
         WHERE user_id = ? AND resource_type = ? AND resource_id IN ({$placeholders})",
        $params
    );

    $map = [];
    foreach ($rows as $row) {
        $map[(int)$row['resource_id']] = $row;
    }
    return $map;
}
