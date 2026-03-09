<?php
/**
 * Custom Secure Document Viewer
 * PDF.js for PDFs
 * Mammoth.js for DOCX (view-only)
 */
if (!isset($view_resource)) {
    return;
}

$resource_id = $view_resource['id'];
$file_path = $view_resource['file_path'] ?? '';
$file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

require_once __DIR__ . '/../config/database.php';
$stream_expires = time() + 300;
$stream_token = hash_hmac('sha256', 'document|' . $resource_id . '|' . $stream_expires . '|' . session_id(), session_id());
$doc_url = '../includes/document_stream.php?id=' . $resource_id . '&exp=' . $stream_expires . '&token=' . urlencode($stream_token);
?>
<div class="custom-document-viewer mb-4">
    <div class="viewer-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><?= htmlspecialchars($view_resource['title']) ?></h5>
            <small><?= htmlspecialchars($view_resource['course_title'] ?? 'No course') ?></small><br><small class="text-light" id="docViewCount"><?= number_format((int)($view_resource['views_count'] ?? 0)) ?> views</small>
        </div>
        <div class="d-flex gap-2">
        <button type="button" id="saveDocOffline" data-url="<?= htmlspecialchars($doc_url) ?>" class="btn btn-light btn-sm">
            <i class="fas fa-cloud-download-alt me-2"></i>Save for offline viewing
        </button>
        <a href="javascript:history.back()" class="btn btn-light btn-sm">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
        </div>
    </div>

    <div class="document-container" id="documentContainer">

    <?php if ($file_ext === 'pdf'): ?>
    <!-- ================= PDF VIEWER ================= -->
    <div class="pdf-viewer-wrapper">
        <div class="pdf-toolbar">
            <div class="toolbar-left">
                <button id="prevPage" class="toolbar-btn" title="Previous page">‹</button>
                <span><span id="pageNum">1</span> / <span id="pageCount">-</span></span>
                <button id="nextPage" class="toolbar-btn" title="Next page">›</button>
                <button id="zoomOut" class="toolbar-btn" title="Zoom out">−</button>
                <span id="zoomLevel">100%</span>
                <button id="zoomIn" class="toolbar-btn" title="Zoom in">+</button>
                <input id="pageJump" class="page-jump" type="number" min="1" value="1" aria-label="Go to page">
            </div>
            <div class="toolbar-right">
                <button id="fitWidth" class="toolbar-btn">Fit Width</button>
                <button id="fitPage" class="toolbar-btn">Fit Page</button>
                <button id="docHelp" class="toolbar-btn" title="How to use viewer">?</button>
                <button id="fullscreenBtn" class="toolbar-btn" title="Toggle fullscreen">⛶</button>
            </div>
        </div>
        <div id="docQuickHelp" class="doc-quick-help hide">
            <strong><i class="fas fa-file-alt me-1"></i>Quick Tips</strong>
            <ul>
                <li><strong>← / →</strong> Previous / Next page</li>
                <li><strong>+ / -</strong> Zoom in / out</li>
                <li><strong>W</strong> Fit width, <strong>P</strong> Fit page</li>
                <li>Use the page box to jump directly to a page</li>
            </ul>
        </div>
        <div class="pdf-canvas-container" id="pdfCanvasContainer">
            <canvas id="pdfCanvas"></canvas>
            <div id="pdfLoading" class="loading-spinner">
                <i class="fas fa-spinner fa-spin fa-3x"></i>
            </div>
        </div>
    </div>

    <?php elseif ($file_ext === 'docx'): ?>
    <!-- ================= DOCX VIEWER ================= -->
    <div class="docx-viewer-wrapper">
        <div class="pdf-toolbar">
            <button id="docxZoomOut" class="toolbar-btn">−</button>
            <span id="docxZoomLevel">100%</span>
            <button id="docxZoomIn" class="toolbar-btn">+</button>
            <button id="docxReset" class="toolbar-btn">Reset</button>
            <button id="docxFullscreen" class="toolbar-btn">⛶</button>
        </div>

        <div id="docxScrollContainer">
            <div id="docxContent" class="docx-content">Loading document…</div>
        </div>
    </div>

    <?php elseif (in_array($file_ext, ['jpg','jpeg','png','gif'])): ?>
    <!-- ================= IMAGE VIEWER ================= -->
    <div class="image-viewer-wrapper">
        <img src="<?= htmlspecialchars($doc_url) ?>" class="document-image" draggable="false">
    </div>

    <?php elseif ($file_ext === 'txt'): ?>
    <!-- ================= TEXT VIEWER ================= -->
    <pre id="textContent" class="text-content">Loading…</pre>

    <?php else: ?>
    <div class="unsupported-viewer p-5 text-center">
        <h5>Preview not available</h5>
        <p class="text-muted">This file type cannot be previewed.</p>
    </div>
    <?php endif; ?>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<style>
.document-container { user-select:none }
.toolbar-btn { border:1px solid #ccc;background:#fff;padding:6px 12px;border-radius:5px;cursor:pointer;font-size:14px }
.toolbar-btn:hover { background:#eee }
.toolbar-btn:active { background:#ddd }
.toolbar-btn:focus-visible { outline:2px solid #667eea; outline-offset:1px }
.pdf-toolbar { display:flex;justify-content:space-between;align-items:center;padding:10px;background:#f5f5f5;flex-wrap:wrap;gap:10px;position:sticky;top:0;z-index:10 }
.toolbar-left, .toolbar-right { display:flex;align-items:center;gap:10px;flex-wrap:wrap }
.pdf-viewer-wrapper { height:80vh;display:flex;flex-direction:column }
.pdf-canvas-container { flex:1;overflow:auto;background:#444;padding:20px;display:flex;justify-content:center;align-items:center }
#pdfCanvas { background:white;box-shadow:0 4px 20px rgba(0,0,0,.3);max-width:100%;max-height:100% }
.loading-spinner { position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) }
.page-jump { width:72px;border:1px solid #ccc;border-radius:5px;padding:4px 8px;font-size:13px }
.doc-quick-help { background:rgba(18,20,35,.92);color:#fff;padding:10px 12px;font-size:12px }
.doc-quick-help ul { margin:6px 0 0; padding-left:16px }
.doc-quick-help.hide { display:none }

/* Fullscreen mode adjustments */
.document-container:fullscreen,
.document-container:-webkit-full-screen,
.document-container:-moz-full-screen {
    width: 100% !important;
    height: 100% !important;
    max-width: 100% !important;
    max-height: 100% !important;
}

#pdfCanvas:fullscreen,
#pdfCanvas:-webkit-full-screen,
#pdfCanvas:-moz-full-screen {
    max-width: 100vw !important;
    max-height: 100vh !important;
}

#docxScrollContainer {
    background:#525252;
    padding:30px 0;
    height:80vh;
    overflow:auto;
}
.docx-content {
    background:white;
    max-width:900px;
    margin:0 auto;
    padding:40px;
    transform-origin:top center;
    box-shadow:0 6px 25px rgba(0,0,0,.25);
}
.docx-content img { max-width:100%;pointer-events:none }

/* Mobile adjustments */
@media (max-width: 768px) {
    .toolbar-btn { padding:8px 12px;font-size:12px }
    .page-jump { width:56px;font-size:12px;padding:4px 6px }
    .pdf-viewer-wrapper { height:calc(100vh - 120px) }
    #docxScrollContainer { height:calc(100vh - 120px) }
    .docx-content { padding:20px }
    .pdf-toolbar { padding:8px;gap:5px }
    .toolbar-left span { font-size:12px }
}

/* Fullscreen mobile improvements */
.document-container:fullscreen .pdf-viewer-wrapper,
.document-container:-webkit-full-screen .pdf-viewer-wrapper,
.document-container:-moz-full-screen .pdf-viewer-wrapper {
    height: 100vh !important;
}

.document-container:fullscreen #docxScrollContainer,
.document-container:-webkit-full-screen #docxScrollContainer,
.document-container:-moz-full-screen #docxScrollContainer {
    height: 100vh !important;
}
</style>

<script>
(() => {
const docUrl = <?= json_encode($doc_url) ?>;
const ext = <?= json_encode($file_ext) ?>;
const resourceId = <?= (int)$resource_id ?>;


// Track a document view (same endpoint used by video)
(async () => {
  try {
    const r = await fetch('../includes/track_view.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: resourceId, type: 'document' })
    });
    const data = await r.json();
    if (data && data.success) {
      const el = document.getElementById('docViewCount');
      if (el) el.textContent = `${(data.views || 0).toLocaleString()} views`;
    }
  } catch (_) {}
})();

// HARD BLOCK DOWNLOAD KEYS
document.addEventListener('contextmenu', e => e.preventDefault());
document.addEventListener('keydown', e => {
    if ((e.ctrlKey||e.metaKey) && ['s','u','a'].includes(e.key.toLowerCase())) e.preventDefault();
    if (e.key === 'F12') e.preventDefault();
});

<?php if ($file_ext === 'pdf'): ?>
pdfjsLib.GlobalWorkerOptions.workerSrc =
'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

let pdfDoc,page=1,scale=1;
const canvas=document.getElementById('pdfCanvas');
const ctx=canvas.getContext('2d');
const pageJump=document.getElementById('pageJump');
const pdfCanvasContainer=document.getElementById('pdfCanvasContainer');
const helpBtn=document.getElementById('docHelp');
const helpBox=document.getElementById('docQuickHelp');

function updatePageUI() {
  pageNum.textContent=page;
  pageCount.textContent=pdfDoc.numPages;
  zoomLevel.textContent=Math.round(scale*100)+'%';
  if (pageJump) pageJump.value = page;
}

function fitWidthScale(pdfPage) {
  const base = pdfPage.getViewport({scale:1});
  const available = Math.max(200, pdfCanvasContainer.clientWidth - 40);
  return available / base.width;
}

function fitPageScale(pdfPage) {
  const base = pdfPage.getViewport({scale:1});
  const availableW = Math.max(200, pdfCanvasContainer.clientWidth - 40);
  const availableH = Math.max(200, pdfCanvasContainer.clientHeight - 40);
  return Math.min(availableW / base.width, availableH / base.height);
}

function render() {
 pdfDoc.getPage(page).then(p=>{
  const v=p.getViewport({scale});
  canvas.width=v.width; canvas.height=v.height;
  p.render({canvasContext:ctx,viewport:v});
  updatePageUI();
 });
}

pdfjsLib.getDocument(docUrl).promise.then(pdf=>{
 pdfDoc=pdf; render();
 pdfLoading.style.display='none';
});

prevPage.onclick=()=>page>1&&(page--,render());
nextPage.onclick=()=>page<pdfDoc.numPages&&(page++,render());
zoomIn.onclick=()=>scale<3&&(scale+=.2,render());
zoomOut.onclick=()=>scale>.5&&(scale-=.2,render());
fitWidth.onclick=()=>pdfDoc.getPage(page).then(p=>{scale=Math.max(.3, Math.min(3, fitWidthScale(p))); render();});
fitPage.onclick=()=>pdfDoc.getPage(page).then(p=>{scale=Math.max(.3, Math.min(3, fitPageScale(p))); render();});

if (pageJump) {
  pageJump.addEventListener('change', () => {
    const target = Math.min(Math.max(parseInt(pageJump.value || '1', 10), 1), pdfDoc.numPages || 1);
    page = target;
    render();
  });
}

if (helpBtn && helpBox) {
  helpBtn.onclick = () => helpBox.classList.toggle('hide');
}

document.addEventListener('keydown', e => {
  const tag = document.activeElement?.tagName?.toLowerCase();
  if (tag === 'input' || tag === 'textarea' || tag === 'select') return;
  if (e.key === 'ArrowLeft') { e.preventDefault(); prevPage.click(); }
  if (e.key === 'ArrowRight') { e.preventDefault(); nextPage.click(); }
  if (e.key === '+') { e.preventDefault(); zoomIn.click(); }
  if (e.key === '-') { e.preventDefault(); zoomOut.click(); }
  if (e.key.toLowerCase() === 'w') { e.preventDefault(); fitWidth.click(); }
  if (e.key.toLowerCase() === 'p') { e.preventDefault(); fitPage.click(); }
  if (e.key.toLowerCase() === 'h' && helpBtn) { e.preventDefault(); helpBtn.click(); }
});
fullscreenBtn.onclick=()=>{
  if (!document.fullscreenElement) {
    documentContainer.requestFullscreen().catch(err => {
      console.error('Fullscreen failed:', err);
      // Fallback for mobile: try webkit fullscreen
      if (documentContainer.webkitRequestFullscreen) {
        documentContainer.webkitRequestFullscreen();
      }
    });
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    }
  }
};

<?php elseif ($file_ext === 'docx'): ?>
let zoom=1;
const content=document.getElementById('docxContent');
const update=()=>{content.style.transform=`scale(${zoom})`;docxZoomLevel.textContent=Math.round(zoom*100)+'%'};

docxZoomIn.onclick=()=>zoom<2.5&&(zoom+=.1,update());
docxZoomOut.onclick=()=>zoom>.6&&(zoom-=.1,update());
docxReset.onclick=()=>{zoom=1;update()};
docxFullscreen.onclick=()=>{
  if (!document.fullscreenElement) {
    documentContainer.requestFullscreen().catch(err => {
      console.error('Fullscreen failed:', err);
      // Fallback for mobile: try webkit fullscreen
      if (documentContainer.webkitRequestFullscreen) {
        documentContainer.webkitRequestFullscreen();
      }
    });
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    }
  }
};

const s=document.createElement('script');
s.src='https://unpkg.com/mammoth/mammoth.browser.min.js';
s.onload=()=>fetch(docUrl).then(r=>r.arrayBuffer())
.then(b=>mammoth.convertToHtml({arrayBuffer:b}))
.then(r=>content.innerHTML=r.value||'<p>No content</p>');
document.body.appendChild(s);

<?php elseif ($file_ext === 'txt'): ?>
fetch(docUrl).then(r=>r.text()).then(t=>textContent.textContent=t);
<?php endif; ?>

const saveDocOfflineBtn = document.getElementById('saveDocOffline');
if (saveDocOfflineBtn) {
  saveDocOfflineBtn.addEventListener('click', async () => {
    const url = saveDocOfflineBtn.dataset.url;
    saveDocOfflineBtn.disabled = true;

    try {
      const registration = await navigator.serviceWorker?.ready;
      const worker = registration?.active;
      if (!worker) throw new Error('No active service worker');

      const messageChannel = new MessageChannel();
      const completed = new Promise((resolve, reject) => {
        messageChannel.port1.onmessage = (event) => {
          if (event.data?.ok) resolve();
          else reject(new Error(event.data?.error || 'Offline save failed'));
        };
      });

      worker.postMessage({ type: 'OFFLINE_MEDIA_SAVE', url }, [messageChannel.port2]);
      await completed;

      saveDocOfflineBtn.innerHTML = '<i class="fas fa-check me-2"></i>Saved Offline';
    } catch (e) {
      console.warn('Offline save failed', e);
      saveDocOfflineBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Retry Offline Save';
      saveDocOfflineBtn.disabled = false;
    }
  });
}

})();
</script>
