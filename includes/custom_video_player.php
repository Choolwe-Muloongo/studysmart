<?php
/**
 * Custom Video Player Component
 * Prevents downloads and provides secure video playback with custom controls
 */
if (!isset($view_video)) {
    return;
}

$video_id = $view_video['id'];
$video_url = null;
$is_external = false;

// Determine video source
if (!empty($view_video['video_url'])) {
    $video_url = $view_video['video_url'];
    $is_external = true;
    // Check if YouTube
    if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video_url, $matches);
        $youtube_id = $matches[1] ?? '';
    }
} elseif (!empty($view_video['file_path'])) {
    // ALWAYS use secure streaming endpoint for uploaded videos
    require_once __DIR__ . '/../config/database.php';
    
    // Use relative path - more reliable than absolute URLs
    // Since we're in includes/ folder, go up one level to get to root
    $video_url = '../includes/video_stream.php?id=' . $video_id;
    $is_external = false;
    
    error_log("Custom Video Player: Using streaming endpoint for video ID {$video_id}");
} else {
    $video_url = null;
}
?>
<div class="custom-video-player mb-4">
    <div class="player-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><?php echo htmlspecialchars($view_video['title']); ?></h5>
            <small><?php echo htmlspecialchars($view_video['course_title'] ?? 'No course'); ?></small>
            <br><small class="text-light" id="viewCount"><?php echo number_format($view_video['views_count'] ?? 0); ?> views</small>
        </div>
        <a href="javascript:history.back()" class="btn btn-light btn-sm">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
    
    <div class="video-container" id="videoContainer">
        <?php if ($is_external && isset($youtube_id) && !empty($youtube_id)): ?>
            <!-- YouTube Embed -->
            <div class="ratio ratio-16x9">
                <iframe 
                    src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtube_id); ?>?rel=0&modestbranding=1" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                    allowfullscreen
                    style="border: none;">
                </iframe>
            </div>
        <?php elseif ($is_external): ?>
            <!-- External Video URL -->
            <div class="ratio ratio-16x9">
                <iframe 
                    src="<?php echo htmlspecialchars($video_url); ?>" 
                    allowfullscreen
                    style="border: none;">
                </iframe>
            </div>
        <?php elseif ($video_url): ?>
            <!-- Custom Secure Video Player with Custom Controls -->
            <div class="custom-video-wrapper">
                <video 
                    id="secureVideoPlayer" 
                    preload="metadata"
                    crossorigin="anonymous"
                    playsinline
                    data-video-id="<?php echo $video_id; ?>"
                    data-stream-url="<?php echo htmlspecialchars($video_url); ?>">
                    <source src="<?php echo htmlspecialchars($video_url); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                
                <!-- Custom Controls -->
                <div class="custom-controls" id="customControls">
                    <div class="progress-container">
                        <div class="progress-bar" id="progressBar">
                            <div class="progress-filled" id="progressFilled"></div>
                        </div>
                    </div>
                    <!-- Gesture handling is bound to the video element directly to avoid covering controls -->
                    
                    <div class="controls-bottom">
                        <div class="controls-left">
                            <button id="skipBackward" class="control-btn" title="Skip -10s">
                                <i class="fas fa-backward"></i>
                            </button>
                            <button id="playPause" class="control-btn play-pause-btn" title="Play/Pause">
                                <i class="fas fa-play"></i>
                            </button>
                            <button id="skipForward" class="control-btn" title="Skip +10s">
                                <i class="fas fa-forward"></i>
                            </button>
                            <div class="volume-container">
                                <button id="muteBtn" class="control-btn" title="Mute/Unmute">
                                    <i class="fas fa-volume-up"></i>
                                </button>
                                <input type="range" id="volumeSlider" min="0" max="1" step="0.01" value="1" title="Volume">
                            </div>
                            <div class="time-display">
                                <span id="currentTime">00:00</span>
                                <span class="separator">/</span>
                                <span id="duration">00:00</span>
                            </div>
                        </div>
                        <div class="controls-right">
                            <button id="rotateBtn" class="control-btn" title="Rotate" aria-label="Rotate video">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button id="fullscreenBtn" class="control-btn" title="Fullscreen">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Floating fullscreen button for touch devices (always available on mobile) -->
                    <button id="fullscreenFloating" class="control-btn fullscreen-floating" title="Fullscreen" aria-label="Toggle Fullscreen (mobile)">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
                
                <div id="videoError" class="alert alert-danger d-none mt-2">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span id="errorMessage">Video failed to load. Please try again.</span>
                </div>

                <div class="alert alert-info mt-2">
                    <small>
                        <strong>Debug:</strong> 
                        <a href="<?php echo htmlspecialchars($video_url); ?>" target="_blank" id="streamUrlLink">Test Streaming URL</a> | 
                        <a href="<?php echo APP_URL; ?>/includes/test_video.php?id=<?php echo $video_id; ?>" target="_blank">View Debug Info</a>
                    </small>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Video source not available. Please contact your instructor.
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($view_video['description'])): ?>
    <div class="p-4 bg-white">
        <h6>Description</h6>
        <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars($view_video['description'])); ?></p>
    </div>
    <?php endif; ?>
</div>

<style>
.custom-video-player {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    overflow: hidden;
}

.player-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem 1.5rem;
}

.video-container {
    background: #000;
    position: relative;
    width: 100%;
}

.custom-video-wrapper {
    position: relative;
    width: 100%;
    background: #000;
}

.custom-video-wrapper video {
    width: 100%;
    display: block;
    max-height: 70vh;
    cursor: pointer;
}

/* Custom Controls */
.custom-controls {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    padding: 15px;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
    z-index: 45; /* ensure controls sit above video */
}

/* Ensure fullscreen button and other controls are above gestures/video */
.controls-right .control-btn, #fullscreenBtn {
    z-index: 60;
    position: relative;
}

/* Floating fullscreen button for touch devices */
.fullscreen-floating {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 12px 14px;
    border-radius: 8px;
    z-index: 100;
    display: none;
    box-shadow: 0 6px 18px rgba(0,0,0,0.35);
    border: 1px solid rgba(255,255,255,0.2);
    font-size: 18px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.fullscreen-floating:active {
    background: rgba(0,0,0,0.9);
    transform: scale(0.95);
}

.fullscreen-floating:hover {
    background: rgba(0,0,0,0.85);
}

/* Show on all touch devices */
@media (hover: none) and (pointer: coarse) {
    .fullscreen-floating { 
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
}

/* Also show on small screens where touch is likely */
@media (max-width: 768px) {
    .fullscreen-floating { 
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        top: 12px;
        right: 12px;
        padding: 14px 16px;
        font-size: 20px;
    }
}

/* Gesture HUD (volume/brightness) */
.gesture-hud {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0,0,0,0.6);
    color: #fff;
    padding: 10px 14px;
    border-radius: 10px;
    display: none;
    align-items: center;
    gap: 8px;
    z-index: 80;
    font-size: 15px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.35);
}
.gesture-hud i { font-size: 16px; }

/* Rotated video support -- toggle with #rotateBtn */
.custom-video-wrapper.rotated {
    display: flex;
    align-items: center;
    justify-content: center;
}
.custom-video-wrapper.rotated video {
    transform: rotate(90deg);
    transform-origin: center center;
    width: auto !important;
    height: 100vh !important;
    max-height: none !important;
    object-fit: contain !important;
}

/* In fullscreen, rotated video should also fit */
.custom-video-wrapper.rotated:-webkit-full-screen video,
.custom-video-wrapper.rotated:fullscreen video,
.custom-video-wrapper.rotated:-moz-full-screen video {
    width: 100vh !important;
    height: 100vw !important;
}

/* Slight rotation icon variant for visual feedback */
.fa-rotate-90 { transform: rotate(90deg); } 

.custom-video-wrapper:hover .custom-controls,
.custom-controls.show {
    opacity: 1;
    pointer-events: all;
}

/* On touch devices (no hover), keep controls visible for discoverability */
@media (hover: none) and (pointer: coarse) {
    .custom-controls {
        opacity: 1;
        pointer-events: all;
    }
}

/* Fullscreen layout adjustments */
.custom-video-wrapper:fullscreen, .custom-video-wrapper:-webkit-full-screen, .custom-video-wrapper:-moz-full-screen {
    width: 100% !important;
    height: 100% !important;
    max-height: none !important;
}
.custom-video-wrapper:fullscreen video, .custom-video-wrapper:-webkit-full-screen video, .custom-video-wrapper:-moz-full-screen video {
    width: 100% !important;
    height: 100% !important;
    object-fit: contain !important;
}
/* Mobile fullscreen enhancements */
@media (max-width: 768px) {
    .custom-video-wrapper:fullscreen,
    .custom-video-wrapper:-webkit-full-screen,
    .custom-video-wrapper:-moz-full-screen {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        z-index: 999999 !important;
    }
}

.progress-container {
    margin-bottom: 10px;
}

.progress-bar {
    width: 100%;
    height: 6px;
    background: rgba(255,255,255,0.3);
    border-radius: 3px;
    cursor: pointer;
    position: relative;
}

.progress-filled {
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 3px;
    width: 0%;
    transition: width 0.1s linear;
}

.controls-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.controls-left {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}

.controls-right {
    display: flex;
    align-items: center;
}

.control-btn {
    background: transparent;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    padding: 8px 12px;
    border-radius: 5px;
    transition: all 0.2s ease;
}

.control-btn:hover {
    background: rgba(255,255,255,0.2);
    transform: scale(1.1);
}

.play-pause-btn {
    font-size: 24px;
}

.volume-container {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: 10px;
}

#volumeSlider {
    width: 80px;
    height: 4px;
    background: rgba(255,255,255,0.3);
    border-radius: 2px;
    outline: none;
    -webkit-appearance: none;
}

#volumeSlider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 12px;
    height: 12px;
    background: #667eea;
    border-radius: 50%;
    cursor: pointer;
}

#volumeSlider::-moz-range-thumb {
    width: 12px;
    height: 12px;
    background: #667eea;
    border-radius: 50%;
    cursor: pointer;
    border: none;
}

.time-display {
    color: white;
    font-size: 14px;
    margin-left: 10px;
    font-family: monospace;
}

.separator {
    margin: 0 5px;
    opacity: 0.7;
}

/* Prevent text selection */
.custom-video-wrapper {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

/* Hide all native default browser controls - custom controls will handle interaction */
#secureVideoPlayer::-webkit-media-controls {
    display: none !important;
}

#secureVideoPlayer::-webkit-media-controls-enclosure {
    display: none !important;
}

#secureVideoPlayer::-moz-media-controls {
    display: none !important;
}
</style>

<script>
(function() {
    const video = document.getElementById('secureVideoPlayer');
    if (!video) return;
    
    const playPauseBtn = document.getElementById('playPause');
    const skipBackwardBtn = document.getElementById('skipBackward');
    const skipForwardBtn = document.getElementById('skipForward');
    const muteBtn = document.getElementById('muteBtn');
    const volumeSlider = document.getElementById('volumeSlider');
    const progressBar = document.getElementById('progressBar');
    const progressFilled = document.getElementById('progressFilled');
    const currentTimeEl = document.getElementById('currentTime');
    const durationEl = document.getElementById('duration');
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    // Improve accessibility and ensure visibility
    if (fullscreenBtn) {
        fullscreenBtn.setAttribute('aria-label', 'Toggle Fullscreen');
        fullscreenBtn.style.zIndex = '60';
        fullscreenBtn.style.position = 'relative';
    }

    // Orientation helpers (attempt to lock to landscape when entering fullscreen)
    function tryLockLandscape() {
        try {
            const so = screen.orientation || screen;
            if (so && so.lock) {
                so.lock('landscape').catch(err => console.warn('Orientation lock failed:', err));
            } else if (screen.lockOrientation) {
                try { screen.lockOrientation('landscape'); } catch (e) { console.warn('Orientation lock (legacy) failed:', e); }
            } else {
                console.warn('Orientation lock not supported in this browser');
            }
        } catch (ex) {
            console.warn('Orientation lock error', ex);
        }
    }
    function tryUnlockOrientation() {
        try {
            const so = screen.orientation || screen;
            if (so && so.unlock) {
                so.unlock();
            } else if (screen.unlockOrientation) {
                try { screen.unlockOrientation(); } catch (e) { console.warn('Orientation unlock (legacy) failed:', e); }
            }
        } catch (ex) {
            console.warn('Orientation unlock error', ex);
        }
    }

    // Mobile floating fullscreen button (visible on touch devices)
    const fullscreenFloating = document.getElementById('fullscreenFloating');
    if (fullscreenFloating) {
        fullscreenFloating.addEventListener('click', () => {
            const wrapper = video.closest('.custom-video-wrapper') || video;
            if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.mozFullScreenElement && !document.msFullscreenElement) {
                // Request fullscreen then attempt to lock orientation
                const fsRequest = wrapper.requestFullscreen?.() 
                    || wrapper.webkitRequestFullscreen?.()
                    || wrapper.mozRequestFullScreen?.()
                    || wrapper.msRequestFullscreen?.()
                    || video.requestFullscreen?.()
                    || null;
                
                if (fsRequest && typeof fsRequest.catch === 'function') {
                    fsRequest.catch(err => {
                        console.error('Fullscreen request failed:', err);
                        if (video.webkitRequestFullscreen) {
                            video.webkitRequestFullscreen();
                        }
                    });
                } else if (!fsRequest && video.webkitRequestFullscreen) {
                    video.webkitRequestFullscreen();
                }
                // Try to lock after a short delay when fullscreen has been entered
                setTimeout(() => tryLockLandscape(), 300);
            } else {
                // Exit fullscreen
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
                // unlock when exiting
                setTimeout(() => tryUnlockOrientation(), 200);
            }
        });
    }

    // Rotate button handler -- toggles a CSS class that rotates the video 90deg
    const rotateBtn = document.getElementById('rotateBtn');
    let isRotated = false;
    if (rotateBtn) {
        rotateBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const wrapper = video.closest('.custom-video-wrapper');
            isRotated = !isRotated;
            if (isRotated) {
                wrapper.classList.add('rotated');
                rotateBtn.innerHTML = '<i class="fas fa-sync-alt fa-rotate-90"></i>';
            } else {
                wrapper.classList.remove('rotated');
                rotateBtn.innerHTML = '<i class="fas fa-sync-alt"></i>';
            }
            // Trigger a resize event after transform to help layout engines recalc
            setTimeout(() => window.dispatchEvent(new Event('resize')), 250);
        });
    }

    // Keep both buttons in sync when fullscreen changes and manage orientation
    document.addEventListener('fullscreenchange', () => {
        const isFS = !!document.fullscreenElement;
        const icon = isFS ? '<i class="fas fa-compress"></i>' : '<i class="fas fa-expand"></i>';
        if (fullscreenBtn) fullscreenBtn.innerHTML = icon;
        if (fullscreenFloating) fullscreenFloating.innerHTML = icon;
        if (isFS) {
            // When entering fullscreen, attempt to lock
            tryLockLandscape();
        } else {
            // When leaving fullscreen, unlock
            tryUnlockOrientation();
        }
    });
    const customControls = document.getElementById('customControls');
    const errorDiv = document.getElementById('videoError');
    
    // Ensure we're using the streaming endpoint
    const streamUrl = video.dataset.streamUrl;
    if (streamUrl) {
        const source = video.querySelector('source');
        if (source) {
            source.src = streamUrl;
            video.load();
        }
    }

    // Detect touch devices and show custom controls on interaction
    const isTouch = ('ontouchstart' in window) || (navigator.maxTouchPoints && navigator.maxTouchPoints > 0);
    if (isTouch) {
        // Make custom controls visible (helpful when paused) and show on touch
        if (customControls) customControls.classList.add('show');
        video.addEventListener('touchstart', showControls);

        // Allow tapping to toggle visibility of custom controls on mobile
        let lastTap = 0;
        video.addEventListener('touchend', (e) => {
            const now = Date.now();
            // prevent accidental double processing
            if (now - lastTap < 250) {
                lastTap = now;
                return;
            }
            lastTap = now;
            if (customControls) customControls.classList.toggle('show');
        });
    }
    
    // Format time
    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
    
    // Update progress bar
    function updateProgress() {
        if (video.duration) {
            const percent = (video.currentTime / video.duration) * 100;
            progressFilled.style.width = percent + '%';
            currentTimeEl.textContent = formatTime(video.currentTime);
        }
    }

    // ----- Seek dragging (pointer events) -----
    let isDragging = false;
    function seekToClientX(clientX) {
        const rect = progressBar.getBoundingClientRect();
        let percent = (clientX - rect.left) / rect.width;
        percent = Math.max(0, Math.min(1, percent));
        video.currentTime = percent * video.duration;
    }

    progressBar.addEventListener('pointerdown', (e) => {
        isDragging = true;
        progressBar.setPointerCapture(e.pointerId);
        seekToClientX(e.clientX);
    });
    document.addEventListener('pointermove', (e) => {
        if (!isDragging) return;
        seekToClientX(e.clientX);
    });
    document.addEventListener('pointerup', (e) => {
        if (isDragging) {
            isDragging = false;
        }
    });

    // ----- Double-tap skip & swipe volume/brightness -----
    let brightness = 1.0; // CSS filter

    function showSkipAnimation(side, amount) {
        const anim = document.createElement('div');
        anim.style.position = 'absolute';
        anim.style.background = 'rgba(0,0,0,0.6)';
        anim.style.color = 'white';
        anim.style.padding = '8px 12px';
        anim.style.borderRadius = '6px';
        anim.style.top = '40%';
        anim.style[side] = '20%';
        anim.style.zIndex = 40;
        anim.innerText = (amount > 0 ? '+' + amount + 's' : amount + 's');
        document.querySelector('.custom-video-wrapper').appendChild(anim);
        setTimeout(() => anim.remove(), 700);
    }

    // Double-tap handling on video element (mobile touch)
    let lastTap = 0;
    video.addEventListener('touchend', (e) => {
        const now = Date.now();
        const rect = video.getBoundingClientRect();
        const t = e.changedTouches[0];
        const side = (t.clientX > rect.left + rect.width / 2) ? 'right' : 'left';
        if (now - lastTap < 350) {
            // double tap detected
            if (side === 'left') {
                video.currentTime = Math.max(0, video.currentTime - 10);
                showSkipAnimation('left', -10);
            } else {
                video.currentTime = Math.min(video.duration, video.currentTime + 10);
                showSkipAnimation('right', 10);
            }
        }
        lastTap = now;
    });

    // Double-click on desktop
    video.addEventListener('dblclick', (e) => {
        const rect = video.getBoundingClientRect();
        const side = (e.clientX > rect.left + rect.width / 2) ? 'right' : 'left';
        if (side === 'left') {
            video.currentTime = Math.max(0, video.currentTime - 10);
            showSkipAnimation('left', -10);
        } else {
            video.currentTime = Math.min(video.duration, video.currentTime + 10);
            showSkipAnimation('right', 10);
        }
    });

    // Swipe volume on right half, brightness on left half (VLC-like behavior)
    let touchActive = false;
    let startY = 0;
    let startVolume = 1;
    let startBrightness = 1;
    let gestureSide = null;
    let hudTimeout = null;

    function createHUD(id, html) {
        let el = document.getElementById(id);
        if (!el) {
            el = document.createElement('div');
            el.id = id;
            el.className = 'gesture-hud';
            document.querySelector('.custom-video-wrapper').appendChild(el);
        }
        el.innerHTML = html;
        el.style.display = 'flex';
        clearTimeout(hudTimeout);
        hudTimeout = setTimeout(() => { el.style.display = 'none'; }, 900);
        return el;
    }

    function handleTouchStart(e) {
        if (!e.touches || e.touches.length !== 1) return;
        const t = e.touches[0];
        startY = t.clientY;
        const rect = video.getBoundingClientRect();
        gestureSide = (t.clientX > rect.left + rect.width / 2) ? 'right' : 'left';
        startVolume = video.volume;
        startBrightness = brightness;
        touchActive = true;
    }

    function handleTouchMove(e) {
        if (!touchActive || !e.touches || e.touches.length !== 1) return;
        // prevent page scroll while performing gesture
        if (e.cancelable) e.preventDefault();
        const t = e.touches[0];
        const rect = video.getBoundingClientRect();
        const dy = startY - t.clientY; // swipe up increases
        const percent = dy / rect.height; // proportion of the video height
        if (gestureSide === 'right') {
            // adjust volume based on percentage of height (smooth and predictable)
            let newVol = Math.max(0, Math.min(1, startVolume + percent));
            video.volume = newVol;
            volumeSlider.value = newVol;
            video.muted = newVol === 0;
            muteBtn.innerHTML = video.muted ? '<i class="fas fa-volume-mute"></i>' : '<i class="fas fa-volume-up"></i>';
            createHUD('volumeHUD', '<i class="fas fa-volume-up"></i> ' + Math.round(newVol * 100) + '%');
        } else {
            // adjust brightness (CSS filter)
            let newB = Math.max(0.2, Math.min(2, startBrightness + percent));
            brightness = newB;
            video.style.filter = 'brightness(' + brightness + ')';
            createHUD('brightnessHUD', '<i class="fas fa-sun"></i> ' + Math.round(brightness * 100) + '%');
        }
    }

    function handleTouchEnd(e) {
        touchActive = false;
    }

    // Use non-passive listeners so we can prevent default page scrolling while adjusting
    video.addEventListener('touchstart', handleTouchStart, {passive:false});
    video.addEventListener('touchmove', handleTouchMove, {passive:false});
    video.addEventListener('touchend', handleTouchEnd);

    // Desktop double-click handling already bound to video element above (no separate gesture overlays)

    
    // Play/Pause
    playPauseBtn.addEventListener('click', () => {
        if (video.paused) {
            video.play();
            playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
        } else {
            video.pause();
            playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
        }
    });

    // Track initial play to increment view count (only once per session/page)
    let hasSentView = false;
    video.addEventListener('play', () => {
        if (!hasSentView) {
            hasSentView = true;
            // Send view to server
            fetch('<?php echo APP_URL; ?>/includes/track_view.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ id: video.dataset.videoId, type: 'video' })
            }).then(r => r.json()).then(data => {
                if (data.success && data.views !== undefined) {
                    const vc = document.getElementById('viewCount');
                    if (vc) vc.textContent = data.views.toLocaleString() + ' views';
                }
            }).catch(err => console.error('View tracking failed', err));
        }
    });
    
    // Skip backward 10 seconds
    skipBackwardBtn.addEventListener('click', () => {
        video.currentTime = Math.max(0, video.currentTime - 10);
    });
    
    // Skip forward 10 seconds
    skipForwardBtn.addEventListener('click', () => {
        video.currentTime = Math.min(video.duration, video.currentTime + 10);
    });
    
    // Mute/Unmute
    muteBtn.addEventListener('click', () => {
        video.muted = !video.muted;
        muteBtn.innerHTML = video.muted 
            ? '<i class="fas fa-volume-mute"></i>' 
            : '<i class="fas fa-volume-up"></i>';
        volumeSlider.value = video.muted ? 0 : video.volume;
    });
    
    // Volume control
    volumeSlider.addEventListener('input', (e) => {
        video.volume = e.target.value;
        video.muted = e.target.value == 0;
        muteBtn.innerHTML = video.muted 
            ? '<i class="fas fa-volume-mute"></i>' 
            : '<i class="fas fa-volume-up"></i>';
    });
    
    // Progress bar click
    progressBar.addEventListener('click', (e) => {
        const rect = progressBar.getBoundingClientRect();
        const percent = (e.clientX - rect.left) / rect.width;
        video.currentTime = percent * video.duration;
    });
    
    // Fullscreen
    fullscreenBtn.addEventListener('click', () => {
        const wrapper = video.closest('.custom-video-wrapper') || video;
        if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.mozFullScreenElement && !document.msFullscreenElement) {
            // Request fullscreen on the wrapper so controls remain visible and layout is preserved
            const fsRequest = wrapper.requestFullscreen?.() 
                || wrapper.webkitRequestFullscreen?.()
                || wrapper.mozRequestFullScreen?.()
                || wrapper.msRequestFullscreen?.()
                || video.requestFullscreen?.()
                || video.webkitRequestFullscreen?.()
                || null;
            
            if (fsRequest && typeof fsRequest.catch === 'function') {
                fsRequest.catch(err => {
                    console.error('Fullscreen request failed:', err);
                    // Fallback: try direct video fullscreen
                    if (video.requestFullscreen) {
                        video.requestFullscreen().catch(e => console.error('Video fullscreen fallback failed:', e));
                    }
                });
            }
            fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
            // Try to lock to landscape shortly after entering fullscreen
            setTimeout(() => tryLockLandscape(), 300);
        } else {
            // Exit fullscreen
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
            fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
            // unlock orientation after exiting fullscreen
            setTimeout(() => tryUnlockOrientation(), 200);
        }
    });
    
    // Video events
    video.addEventListener('loadedmetadata', () => {
        durationEl.textContent = formatTime(video.duration);
    });
    
    video.addEventListener('timeupdate', updateProgress);
    
    video.addEventListener('play', () => {
        playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
    });
    
    video.addEventListener('pause', () => {
        playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
    });
    
    video.addEventListener('ended', () => {
        playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
        video.currentTime = 0;
    });
    
    // Error handling
    video.addEventListener('error', (e) => {
        console.error('Video error:', video.error);
        console.error('Video error code:', video.error?.code);
        console.error('Video error message:', video.error?.message);
        console.error('Video source:', video.querySelector('source')?.src || video.src);
        
        if (errorDiv) {
            errorDiv.classList.remove('d-none');
            const errorMsg = document.getElementById('errorMessage');
            if (errorMsg) {
                let message = 'Video failed to load. ';
                if (video.error) {
                    switch(video.error.code) {
                        case video.error.MEDIA_ERR_ABORTED:
                            message += 'Video playback was aborted.';
                            break;
                        case video.error.MEDIA_ERR_NETWORK:
                            message += 'Network error occurred. Check if the streaming endpoint is accessible.';
                            break;
                        case video.error.MEDIA_ERR_DECODE:
                            message += 'Video decoding failed. The video format may not be supported.';
                            break;
                        case video.error.MEDIA_ERR_SRC_NOT_SUPPORTED:
                            message += 'Video format not supported or file not found.';
                            break;
                        default:
                            message += 'Unknown error occurred.';
                    }
                }
                message += ' <a href="<?php echo APP_URL; ?>/includes/test_video.php?id=<?php echo $video_id; ?>" target="_blank" style="color: inherit; text-decoration: underline;">Debug Info</a>';
                errorMsg.innerHTML = message;
            }
        }
    });
    
    // Show controls on video click
    video.addEventListener('click', () => {
        if (video.paused) {
            video.play();
        } else {
            video.pause();
        }
    });
    
    // Show/hide controls
    let controlsTimeout;
    function showControls() {
        customControls.classList.add('show');
        clearTimeout(controlsTimeout);
        controlsTimeout = setTimeout(() => {
            if (!video.paused) {
                customControls.classList.remove('show');
            }
        }, 3000);
    }
    
    video.addEventListener('mouseenter', showControls);
    video.addEventListener('mousemove', showControls);
    video.addEventListener('mouseleave', () => {
        if (!video.paused) {
            customControls.classList.remove('show');
        }
    });
    
    // Download protection
    video.addEventListener('contextmenu', (e) => {
        e.preventDefault();
        return false;
    });
    
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S' || e.key === 'u' || e.key === 'U')) {
            e.preventDefault();
            return false;
        }
        if (e.key === 'F12') {
            e.preventDefault();
            return false;
        }
    });
    
    video.addEventListener('dragstart', (e) => {
        e.preventDefault();
        return false;
    });
    
    video.removeAttribute('download');
    
    // Disable picture-in-picture
    if (video.requestPictureInPicture) {
        video.addEventListener('enterpictureinpicture', () => {
            document.exitPictureInPicture();
        });
    }
    
    console.log('Custom video player initialized with streaming endpoint:', streamUrl);
})();
</script>
