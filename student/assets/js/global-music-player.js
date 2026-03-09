(function () {
  if (window.StudySmartMusicPlayer) return;

  const STORAGE_KEY = 'studysmart_global_music_player_v1';
  const OVERLAY_KEY = 'studysmart_music_overlay_enabled';
  const defaultState = { queue: [], currentIndex: -1, currentTime: 0, paused: true, volume: 0.8 };

  const parseState = () => {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return { ...defaultState };
      const parsed = JSON.parse(raw);
      return {
        queue: Array.isArray(parsed.queue) ? parsed.queue : [],
        currentIndex: Number.isInteger(parsed.currentIndex) ? parsed.currentIndex : -1,
        currentTime: Number(parsed.currentTime) || 0,
        paused: typeof parsed.paused === 'boolean' ? parsed.paused : true,
        volume: Math.max(0, Math.min(1, Number(parsed.volume) || 0.8))
      };
    } catch (_) { return { ...defaultState }; }
  };

  const state = parseState();
  const audio = new Audio();
  audio.preload = 'metadata';
  audio.volume = state.volume;

  const style = document.createElement('style');
  style.textContent = `#global-music-player-overlay{position:fixed;right:16px;bottom:16px;width:300px;max-width:calc(100vw - 24px);z-index:1100;background:#121726;color:#fff;border-radius:12px;padding:10px 12px;box-shadow:0 10px 30px rgba(0,0,0,.35);font-family:inherit}#global-music-player-overlay.hidden{display:none}#global-music-player-overlay .gmp-title{font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:8px}#global-music-player-overlay .gmp-controls{display:flex;gap:6px;justify-content:center;margin-bottom:8px}#global-music-player-overlay .gmp-btn{border:none;background:#26324a;color:#fff;border-radius:999px;width:34px;height:34px;cursor:pointer}#global-music-player-overlay .gmp-btn-primary{background:#5a7dff}#global-music-player-overlay .gmp-seek,#global-music-player-overlay .gmp-volume{width:100%}#global-music-player-overlay .gmp-meta{display:flex;justify-content:space-between;align-items:center;gap:10px;font-size:12px;margin-top:6px}#global-music-player-overlay .gmp-volume-wrap{display:flex;align-items:center;gap:6px;min-width:110px}@media (max-width: 575px){#global-music-player-overlay{left:12px;right:12px;bottom:12px;width:auto}}`;
  document.head.appendChild(style);

  const overlay = document.createElement('div');
  overlay.id = 'global-music-player-overlay';
  overlay.innerHTML = '<div class="gmp-title" title="No track">No track selected</div><div class="gmp-controls"><button type="button" class="gmp-btn" data-act="prev"><i class="fas fa-backward"></i></button><button type="button" class="gmp-btn gmp-btn-primary" data-act="toggle"><i class="fas fa-play"></i></button><button type="button" class="gmp-btn" data-act="next"><i class="fas fa-forward"></i></button></div><input class="gmp-seek" type="range" min="0" max="100" step="0.1" value="0" /><div class="gmp-meta"><span class="gmp-time">0:00 / 0:00</span><div class="gmp-volume-wrap"><i class="fas fa-volume-up"></i><input class="gmp-volume" type="range" min="0" max="1" step="0.01" value="' + state.volume + '" /></div></div>';
  document.body.appendChild(overlay);

  const el = { title: overlay.querySelector('.gmp-title'), playPauseBtn: overlay.querySelector('[data-act="toggle"]'), seek: overlay.querySelector('.gmp-seek'), time: overlay.querySelector('.gmp-time'), volume: overlay.querySelector('.gmp-volume') };
  const getOverlayEnabled = () => { const raw = localStorage.getItem(OVERLAY_KEY); return raw === null ? true : raw === '1'; };
  const fmt = (s) => { if (!isFinite(s) || s < 0) return '0:00'; const m = Math.floor(s / 60); const sec = Math.floor(s % 60).toString().padStart(2, '0'); return `${m}:${sec}`; };
  const currentTrack = () => state.queue[state.currentIndex] || null;

  const persist = () => {
    const safeIndex = state.currentIndex >= 0 && state.currentIndex < state.queue.length ? state.currentIndex : -1;
    localStorage.setItem(STORAGE_KEY, JSON.stringify({ queue: state.queue, currentIndex: safeIndex, currentTime: audio.currentTime || state.currentTime || 0, paused: audio.paused, volume: audio.volume }));
  };

  const updateUI = () => {
    const track = currentTrack();
    el.title.textContent = track ? (track.title || 'Untitled track') : 'No track selected';
    el.title.title = el.title.textContent;
    el.playPauseBtn.innerHTML = '<i class="fas ' + (audio.paused ? 'fa-play' : 'fa-pause') + '"></i>';
    const dur = audio.duration || 0;
    const cur = audio.currentTime || 0;
    el.seek.value = dur > 0 ? ((cur / dur) * 100) : 0;
    el.time.textContent = `${fmt(cur)} / ${fmt(dur)}`;
    el.volume.value = audio.volume;
    overlay.classList.toggle('hidden', !getOverlayEnabled());
  };

  const loadTrackAt = (index, autoplay) => {
    if (index < 0 || index >= state.queue.length) return;
    state.currentIndex = index;
    const track = currentTrack();
    if (!track || !track.url) return;
    if (audio.src !== track.url) audio.src = track.url;
    if (state.currentTime > 0) {
      const restoreTime = state.currentTime;
      const handler = () => { audio.currentTime = restoreTime; audio.removeEventListener('loadedmetadata', handler); };
      audio.addEventListener('loadedmetadata', handler);
    }
    if (autoplay) audio.play().catch(function(){});
    updateUI();
    persist();
  };

  const api = {
    enqueue: function (track, playNow) {
      if (!track || !track.url) return;
      const idx = state.queue.findIndex((t) => t.id === track.id && t.url === track.url);
      if (idx === -1) {
        state.queue.push(track);
        if (state.currentIndex === -1) state.currentIndex = 0;
      }
      if (playNow) { state.currentTime = 0; loadTrackAt(idx === -1 ? state.queue.length - 1 : idx, true); }
      persist();
      updateUI();
    },
    enqueueMany: function (tracks, startTrackId) {
      if (!Array.isArray(tracks) || !tracks.length) return;
      tracks.forEach((t) => { if (t && t.url && !state.queue.some((q) => q.id === t.id && q.url === t.url)) state.queue.push(t); });
      if (state.currentIndex === -1) state.currentIndex = 0;
      if (startTrackId != null) {
        const i = state.queue.findIndex((q) => String(q.id) === String(startTrackId));
        if (i >= 0) { state.currentTime = 0; loadTrackAt(i, true); }
      }
      persist();
      updateUI();
    },
    play: function () { audio.play().catch(function(){}); persist(); updateUI(); },
    pause: function () { audio.pause(); persist(); updateUI(); },
    toggle: function () { audio.paused ? api.play() : api.pause(); },
    next: function () { if (!state.queue.length) return; state.currentTime = 0; loadTrackAt((state.currentIndex + 1) % state.queue.length, true); },
    prev: function () { if (!state.queue.length) return; state.currentTime = 0; loadTrackAt((state.currentIndex - 1 + state.queue.length) % state.queue.length, true); },
    seek: function (percent) { if (!audio.duration) return; audio.currentTime = (Math.max(0, Math.min(100, percent)) / 100) * audio.duration; persist(); updateUI(); },
    setVolume: function (v) { audio.volume = Math.max(0, Math.min(1, v)); persist(); updateUI(); },
    setOverlayEnabled: function (enabled) { localStorage.setItem(OVERLAY_KEY, enabled ? '1' : '0'); updateUI(); },
    isOverlayEnabled: getOverlayEnabled
  };

  overlay.addEventListener('click', function (e) {
    const node = e.target.closest('[data-act]'); if (!node) return; const act = node.getAttribute('data-act');
    if (act === 'toggle') api.toggle(); else if (act === 'next') api.next(); else if (act === 'prev') api.prev();
  });
  el.seek.addEventListener('input', () => api.seek(parseFloat(el.seek.value)));
  el.volume.addEventListener('input', () => api.setVolume(parseFloat(el.volume.value)));

  audio.addEventListener('timeupdate', () => { state.currentTime = audio.currentTime || 0; updateUI(); persist(); });
  audio.addEventListener('play', updateUI);
  audio.addEventListener('pause', () => { state.currentTime = audio.currentTime || 0; updateUI(); persist(); });
  audio.addEventListener('ended', () => api.next());

  window.StudySmartMusicPlayer = api;

  if (state.currentIndex >= 0 && state.currentIndex < state.queue.length) loadTrackAt(state.currentIndex, !state.paused);
  else updateUI();

  document.querySelectorAll('[data-global-music-track]').forEach((btn) => {
    btn.addEventListener('click', function () {
      try { api.enqueue(JSON.parse(btn.getAttribute('data-global-music-track')), true); } catch (_) {}
    });
  });
})();
