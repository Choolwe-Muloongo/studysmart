<?php
/**
 * Custom Document Viewer Component
 * Uses PDF.js for PDFs and prevents downloads
 */
if (!isset($view_resource)) {
    return;
}

$resource_id = $view_resource['id'];
$file_path = $view_resource['file_path'] ?? '';
$file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

// Use secure document streaming endpoint
require_once __DIR__ . '/../config/database.php';
$doc_url = '../includes/document_stream.php?id=' . $resource_id;
?>
<div class="custom-document-viewer mb-4">
    <div class="viewer-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><?php echo htmlspecialchars($view_resource['title']); ?></h5>
            <small><?php echo htmlspecialchars($view_resource['course_title'] ?? 'No course'); ?></small>
        </div>
        <a href="javascript:history.back()" class="btn btn-light btn-sm">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
    
    <div class="document-container" id="documentContainer">
        <?php if ($file_ext === 'pdf'): ?>
            <!-- PDF Viewer using PDF.js -->
            <div class="pdf-viewer-wrapper">
                <div class="pdf-toolbar">
                    <div class="toolbar-left">
                        <button id="prevPage" class="toolbar-btn" title="Previous Page">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="page-info">
                            <span id="pageNum">1</span> / <span id="pageCount">-</span>
                        </span>
                        <button id="nextPage" class="toolbar-btn" title="Next Page">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <button id="zoomOut" class="toolbar-btn" title="Zoom Out">
                            <i class="fas fa-search-minus"></i>
                        </button>
                        <span class="zoom-level" id="zoomLevel">100%</span>
                        <button id="zoomIn" class="toolbar-btn" title="Zoom In">
                            <i class="fas fa-search-plus"></i>
                        </button>
                    </div>
                    <div class="toolbar-right">
                        <button id="fitWidth" class="toolbar-btn" title="Fit to Width">
                            <i class="fas fa-arrows-alt-h"></i>
                        </button>
                        <button id="fitPage" class="toolbar-btn" title="Fit to Page">
                            <i class="fas fa-expand"></i>
                        </button>
                        <button id="fullscreenBtn" class="toolbar-btn" title="Fullscreen">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="pdf-canvas-container" id="pdfCanvasContainer">
                    <canvas id="pdfCanvas"></canvas>
                    <div id="pdfLoading" class="loading-spinner">
                        <i class="fas fa-spinner fa-spin fa-3x"></i>
                        <p>Loading document...</p>
                    </div>
                </div>
            </div>
        <?php elseif (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
            <!-- Image Viewer -->
            <div class="image-viewer-wrapper">
                <img src="<?php echo htmlspecialchars($doc_url); ?>" alt="<?php echo htmlspecialchars($view_resource['title']); ?>" id="documentImage" class="document-image">
            </div>
        <?php elseif (in_array($file_ext, ['txt'])): ?>
            <!-- Text Viewer -->
            <div class="text-viewer-wrapper">
                <pre id="textContent" class="text-content">Loading...</pre>
            </div>
        <?php else: ?>
            <!-- Unsupported format message -->
            <div class="unsupported-viewer p-5 text-center">
                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                <h5>Preview not available</h5>
                <p class="text-muted">This file type (<?php echo strtoupper($file_ext); ?>) cannot be previewed in the browser.</p>
                <p class="text-muted small">For security reasons, direct downloads are disabled.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($view_resource['description'])): ?>
    <div class="p-4 bg-white">
        <h6>Description</h6>
        <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars($view_resource['description'])); ?></p>
    </div>
    <?php endif; ?>
</div>

<!-- PDF.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<style>
.custom-document-viewer {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    overflow: hidden;
}

.viewer-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem 1.5rem;
}

.document-container {
    background: #f5f5f5;
    min-height: 70vh;
    position: relative;
}

/* PDF Viewer Styles */
.pdf-viewer-wrapper {
    display: flex;
    flex-direction: column;
    height: 80vh;
}

.pdf-toolbar {
    background: #fff;
    border-bottom: 1px solid #ddd;
    padding: 10px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.toolbar-left, .toolbar-right {
    display: flex;
    align-items: center;
    gap: 10px;
}

.toolbar-btn {
    background: transparent;
    border: 1px solid #ddd;
    padding: 6px 12px;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.2s;
    color: #333;
}

.toolbar-btn:hover {
    background: #f0f0f0;
    border-color: #667eea;
    color: #667eea;
}

.toolbar-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-info {
    font-weight: 500;
    margin: 0 10px;
}

.zoom-level {
    font-weight: 500;
    margin: 0 10px;
    min-width: 50px;
    text-align: center;
}

.pdf-canvas-container {
    flex: 1;
    overflow: auto;
    background: #525252;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 20px;
    position: relative;
}

#pdfCanvas {
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    background: white;
    margin: 0 auto;
}

.loading-spinner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: white;
}

.loading-spinner.hidden {
    display: none;
}

/* Image Viewer */
.image-viewer-wrapper {
    padding: 20px;
    text-align: center;
    background: #525252;
    min-height: 70vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.document-image {
    max-width: 100%;
    max-height: 80vh;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    border-radius: 5px;
}

/* Text Viewer */
.text-viewer-wrapper {
    padding: 20px;
    background: white;
    min-height: 70vh;
}

.text-content {
    font-family: 'Courier New', monospace;
    font-size: 14px;
    line-height: 1.6;
    white-space: pre-wrap;
    word-wrap: break-word;
    margin: 0;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 5px;
    max-height: 80vh;
    overflow: auto;
}

.unsupported-viewer {
    background: white;
    min-height: 70vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

/* Prevent text selection and downloads */
.document-container {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

.document-container img {
    pointer-events: none;
    -webkit-touch-callout: none;
}
</style>

<script>
(function() {
    const docUrl = <?php echo json_encode($doc_url); ?>;
    const fileExt = <?php echo json_encode($file_ext); ?>;
    const resourceId = <?php echo json_encode($resource_id); ?>;
    
    // Download protection
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });
    
    document.addEventListener('keydown', function(e) {
        // Disable Ctrl+S, Ctrl+Shift+S, Ctrl+U, Ctrl+A (select all)
        if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S' || e.key === 'u' || e.key === 'U' || e.key === 'a' || e.key === 'A')) {
            e.preventDefault();
            return false;
        }
        if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Prevent drag and drop
    document.addEventListener('dragstart', function(e) {
        e.preventDefault();
        return false;
    });
    
    <?php if ($file_ext === 'pdf'): ?>
    // PDF.js Implementation
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    
    let pdfDoc = null;
    let pageNum = 1;
    let pageRendering = false;
    let pageNumPending = null;
    let scale = 1.0;
    const canvas = document.getElementById('pdfCanvas');
    const ctx = canvas.getContext('2d');
    const loadingSpinner = document.getElementById('pdfLoading');
    
    function renderPage(num) {
        pageRendering = true;
        pdfDoc.getPage(num).then(function(page) {
            const viewport = page.getViewport({scale: scale});
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            
            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };
            const renderTask = page.render(renderContext);
            
            renderTask.promise.then(function() {
                pageRendering = false;
                if (pageNumPending !== null) {
                    renderPage(pageNumPending);
                    pageNumPending = null;
                }
                updatePageInfo();
            });
        });
    }
    
    function queueRenderPage(num) {
        if (pageRendering) {
            pageNumPending = num;
        } else {
            renderPage(num);
        }
    }
    
    function updatePageInfo() {
        document.getElementById('pageNum').textContent = pageNum;
        document.getElementById('pageCount').textContent = pdfDoc.numPages;
        document.getElementById('zoomLevel').textContent = Math.round(scale * 100) + '%';
        
        document.getElementById('prevPage').disabled = pageNum <= 1;
        document.getElementById('nextPage').disabled = pageNum >= pdfDoc.numPages;
    }
    
    function onPrevPage() {
        if (pageNum <= 1) return;
        pageNum--;
        queueRenderPage(pageNum);
    }
    
    function onNextPage() {
        if (pageNum >= pdfDoc.numPages) return;
        pageNum++;
        queueRenderPage(pageNum);
    }
    
    function onZoomIn() {
        if (scale >= 3.0) return;
        scale += 0.25;
        queueRenderPage(pageNum);
    }
    
    function onZoomOut() {
        if (scale <= 0.5) return;
        scale -= 0.25;
        queueRenderPage(pageNum);
    }
    
    function onFitWidth() {
        if (!pdfDoc) return;
        pdfDoc.getPage(pageNum).then(function(page) {
            const container = document.getElementById('pdfCanvasContainer');
            const containerWidth = container.clientWidth - 40;
            const viewport = page.getViewport({scale: 1.0});
            scale = containerWidth / viewport.width;
            queueRenderPage(pageNum);
        });
    }
    
    function onFitPage() {
        if (!pdfDoc) return;
        pdfDoc.getPage(pageNum).then(function(page) {
            const container = document.getElementById('pdfCanvasContainer');
            const containerWidth = container.clientWidth - 40;
            const containerHeight = container.clientHeight - 40;
            const viewport = page.getViewport({scale: 1.0});
            const scaleX = containerWidth / viewport.width;
            const scaleY = containerHeight / viewport.height;
            scale = Math.min(scaleX, scaleY);
            queueRenderPage(pageNum);
        });
    }
    
    function onFullscreen() {
        const container = document.getElementById('documentContainer');
        if (!document.fullscreenElement) {
            container.requestFullscreen().catch(err => {
                console.error('Error attempting to enable fullscreen:', err);
            });
        } else {
            document.exitFullscreen();
        }
    }
    
    // Event listeners
    document.getElementById('prevPage').addEventListener('click', onPrevPage);
    document.getElementById('nextPage').addEventListener('click', onNextPage);
    document.getElementById('zoomIn').addEventListener('click', onZoomIn);
    document.getElementById('zoomOut').addEventListener('click', onZoomOut);
    document.getElementById('fitWidth').addEventListener('click', onFitWidth);
    document.getElementById('fitPage').addEventListener('click', onFitPage);
    document.getElementById('fullscreenBtn').addEventListener('click', onFullscreen);
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        
        if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
            e.preventDefault();
            onPrevPage();
        } else if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
            e.preventDefault();
            onNextPage();
        } else if (e.key === '+' || e.key === '=') {
            e.preventDefault();
            onZoomIn();
        } else if (e.key === '-') {
            e.preventDefault();
            onZoomOut();
        }
    });
    
    // Load PDF
    loadingSpinner.classList.remove('hidden');
    pdfjsLib.getDocument(docUrl).promise.then(function(pdf) {
        pdfDoc = pdf;
        document.getElementById('pageCount').textContent = pdf.numPages;
        loadingSpinner.classList.add('hidden');
        renderPage(pageNum);
    }).catch(function(error) {
        console.error('Error loading PDF:', error);
        loadingSpinner.innerHTML = '<i class="fas fa-exclamation-triangle fa-3x text-danger"></i><p>Error loading PDF document</p>';
    });
    
    <?php elseif (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
    // Image viewer - prevent right-click and download
    const img = document.getElementById('documentImage');
    if (img) {
        img.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });
        
        img.addEventListener('dragstart', function(e) {
            e.preventDefault();
            return false;
        });
        
        // Prevent image saving
        img.setAttribute('draggable', 'false');
    }
    
    <?php elseif ($file_ext === 'txt'): ?>
    // Text viewer - load content via fetch
    fetch(docUrl)
        .then(response => response.text())
        .then(text => {
            document.getElementById('textContent').textContent = text;
        })
        .catch(error => {
            console.error('Error loading text:', error);
            document.getElementById('textContent').textContent = 'Error loading document content.';
        });
    <?php endif; ?>
    
    console.log('Custom document viewer initialized for:', fileExt);
})();
</script>
