<?php
/**
 * StudySmart video player component (clean UI)
 */
if (!isset($view_video)) {
    return;
}

$video_id = (int)$view_video['id'];
$stream_expires = time() + 300;
$stream_token = hash_hmac('sha256', 'video|' . $video_id . '|' . $stream_expires . '|' . session_id(), session_id());
$source_type = 'none';
$youtube_id = '';
$video_url = '';

if (!empty($view_video['video_url'])) {
    $video_url = trim($view_video['video_url']);
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video_url, $matches)) {
        $youtube_id = $matches[1] ?? '';
        $source_type = $youtube_id !== '' ? 'youtube' : 'external';
    } else {
        $source_type = 'external';
    }
} elseif (!empty($view_video['file_path'])) {
    $video_url = '../includes/video_stream.php?id=' . $video_id . '&exp=' . $stream_expires . '&token=' . urlencode($stream_token);
    $source_type = 'stream';
}
?>

<div class="ss-player mb-4 orientation-auto" id="studySmartPlayer">
    <div class="ss-player__header d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div>
            <h5 class="mb-0"><?php echo htmlspecialchars($view_video['title']); ?></h5>
            <small><?php echo htmlspecialchars($view_video['course_title'] ?? ''); ?> · <?php echo number_format((int)($view_video['views_count'] ?? 0)); ?> views</small>
        </div>
        <div class="ss-player__actions">
            <button class="btn btn-outline-light btn-sm" type="button" onclick="ssSetOrientation('portrait')">
                <i class="fas fa-mobile-alt me-1"></i>Portrait
            </button>
            <button class="btn btn-outline-light btn-sm" type="button" onclick="ssSetOrientation('landscape')">
                <i class="fas fa-tablet-alt me-1"></i>Landscape
            </button>
            <button class="btn btn-light btn-sm" id="ssFitBtn" type="button" onclick="ssToggleFitScreen()">
                <i class="fas fa-expand me-1"></i>Fit Screen
            </button>
            <?php if ($source_type === 'stream' && $video_url !== ''): ?>
            <button class="btn btn-light btn-sm" id="saveVideoOffline" type="button" data-url="<?php echo htmlspecialchars($video_url); ?>">
                <i class="fas fa-cloud-download-alt me-1"></i>Save for offline viewing
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="ss-player__surface">
        <?php if ($source_type === 'youtube' && $youtube_id !== ''): ?>
            <div class="ratio ratio-16x9 w-100" id="ssRatioWrap">
                <iframe
                    src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtube_id); ?>?rel=0&modestbranding=1&playsinline=1&iv_load_policy=3"
                    title="StudySmart video player"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allowfullscreen></iframe>
            </div>
        <?php elseif ($source_type === 'external' && $video_url !== ''): ?>
            <div class="ratio ratio-16x9 w-100" id="ssRatioWrap">
                <iframe
                    src="<?php echo htmlspecialchars($video_url); ?>"
                    title="External video"
                    allowfullscreen></iframe>
            </div>
        <?php elseif ($source_type === 'stream' && $video_url !== ''): ?>
            <video id="ssVideo" controls playsinline controlsList="nodownload noplaybackrate">
                <source src="<?php echo htmlspecialchars($video_url); ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        <?php else: ?>
            <div class="alert alert-warning m-3 mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Video source not available. Please contact your instructor.
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($view_video['description'])): ?>
        <div class="ss-player__meta p-4">
            <h6>Description</h6>
            <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars($view_video['description'])); ?></p>
        </div>
    <?php endif; ?>
</div>

<style>
.ss-player {
    background: #0f172a;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 12px 36px rgba(15, 23, 42, 0.28);
}
.ss-player__header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 1rem 1.25rem;
}
.ss-player__actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.ss-player__actions .btn {
    min-height: 44px;
    border-radius: 10px;
    font-weight: 600;
}
.ss-player__surface {
    background: #000;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}
.ss-player__surface iframe,
.ss-player__surface video {
    width: 100%;
    border: 0;
    max-height: 70vh;
    background: #000;
}
.ss-player__meta {
    background: #fff;
    border-top: 1px solid #e2e8f0;
}

.ss-player.orientation-portrait .ss-player__surface {
    aspect-ratio: 9 / 16;
    max-width: min(100%, 520px);
    margin: 0 auto;
}
.ss-player.orientation-landscape .ss-player__surface,
.ss-player.orientation-auto .ss-player__surface {
    aspect-ratio: 16 / 9;
}

.ss-player.fit-screen {
    position: fixed;
    inset: 0;
    z-index: 1080;
    border-radius: 0;
    display: flex;
    flex-direction: column;
    margin: 0 !important;
    background: #020617;
}
.ss-player.fit-screen .ss-player__header,
.ss-player.fit-screen .ss-player__meta {
    background: rgba(15, 23, 42, 0.9);
}
.ss-player.fit-screen .ss-player__surface {
    flex: 1;
    max-width: 100%;
}
.ss-player.fit-screen .ss-player__surface iframe,
.ss-player.fit-screen .ss-player__surface video {
    height: 100%;
    max-height: none;
    object-fit: contain;
}

@media (max-width: 768px) {
    .ss-player__header {
        padding: 0.9rem 1rem;
    }
}
</style>

<script>
(function () {
    const player = document.getElementById('studySmartPlayer');
    const fitBtn = document.getElementById('ssFitBtn');
    const saveVideoOfflineBtn = document.getElementById('saveVideoOffline');
    if (!player || !fitBtn) return;

    if (saveVideoOfflineBtn) {
        saveVideoOfflineBtn.addEventListener('click', async () => {
            saveVideoOfflineBtn.disabled = true;
            try {
                const registration = await navigator.serviceWorker?.ready;
                const worker = registration?.active;
                if (!worker) throw new Error('No active service worker');

                const channel = new MessageChannel();
                const done = new Promise((resolve, reject) => {
                    channel.port1.onmessage = (event) => {
                        if (event.data?.ok) resolve();
                        else reject(new Error(event.data?.error || 'Offline save failed'));
                    };
                });

                worker.postMessage({ type: 'OFFLINE_MEDIA_SAVE', url: saveVideoOfflineBtn.dataset.url }, [channel.port2]);
                await done;
                saveVideoOfflineBtn.innerHTML = '<i class=\"fas fa-check me-1\"></i>Saved Offline';
            } catch (error) {
                console.warn('Video offline save failed', error);
                saveVideoOfflineBtn.innerHTML = '<i class=\"fas fa-exclamation-triangle me-1\"></i>Retry Offline Save';
                saveVideoOfflineBtn.disabled = false;
            }
        });
    }

    window.ssSetOrientation = function (mode) {
        player.classList.remove('orientation-auto', 'orientation-portrait', 'orientation-landscape');
        player.classList.add('orientation-' + mode);
    };

    window.ssToggleFitScreen = function () {
        const active = player.classList.toggle('fit-screen');
        fitBtn.innerHTML = active
            ? '<i class="fas fa-compress me-1"></i>Exit Fit'
            : '<i class="fas fa-expand me-1"></i>Fit Screen';
        document.body.style.overflow = active ? 'hidden' : '';
    };
})();
</script>
