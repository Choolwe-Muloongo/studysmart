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
                            <button id="fullscreenBtn" class="control-btn" title="Fullscreen">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
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
}

.custom-video-wrapper:hover .custom-controls,
.custom-controls.show {
    opacity: 1;
    pointer-events: all;
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

/* Hide default controls */
#secureVideoPlayer::-webkit-media-controls {
    display: none !important;
}

#secureVideoPlayer::-webkit-media-controls-enclosure {
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
        if (!document.fullscreenElement) {
            video.requestFullscreen().catch(err => {
                console.error('Error attempting to enable fullscreen:', err);
            });
            fullscreenBtn.innerHTML = '<i class="fas fa-compress"></i>';
        } else {
            document.exitFullscreen();
            fullscreenBtn.innerHTML = '<i class="fas fa-expand"></i>';
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
