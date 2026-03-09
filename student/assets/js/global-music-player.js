(function () {
  if (window.StudySmartMusicPlayer) return;

  const STORAGE_KEY = 'studysmart_global_music_player_v2';
  const LIBRARY_META_KEY = 'studysmart_music_library_meta_v1';
  const OVERLAY_KEY = 'studysmart_music_overlay_enabled';
  const IDB_NAME = 'studysmart_music_library';
  const IDB_STORE = 'tracks';

  const defaultState = {
    queue: [], currentIndex: -1, currentTime: 0, paused: true, volume: 0.8,
    playlists: [], activePlaylistId: null,
    library: { query: '', sortBy: 'name', groupByFolder: false }
  };

  const parseState = () => {
    try {
      const parsed = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
      return {
        ...defaultState,
        ...parsed,
        queue: Array.isArray(parsed.queue) ? parsed.queue : [],
        playlists: Array.isArray(parsed.playlists) ? parsed.playlists : [],
        library: { ...defaultState.library, ...(parsed.library || {}) },
        volume: Math.max(0, Math.min(1, Number(parsed.volume) || defaultState.volume))
      };
    } catch (_) { return { ...defaultState }; }
  };

  const state = parseState();
  const audio = new Audio();
  audio.preload = 'metadata';
  audio.volume = state.volume;

  const runtimeFileMap = new Map();
  let libraryTracks = [];

  const uid = () => (crypto.randomUUID ? crypto.randomUUID() : String(Date.now() + Math.random()));
  const norm = (value) => (value || '').toString().trim();

  const openLibraryDb = () => new Promise((resolve, reject) => {
    if (!window.indexedDB) return reject(new Error('IndexedDB unsupported'));
    const req = indexedDB.open(IDB_NAME, 1);
    req.onupgradeneeded = () => {
      const db = req.result;
      if (!db.objectStoreNames.contains(IDB_STORE)) db.createObjectStore(IDB_STORE, { keyPath: 'id' });
    };
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
  });

  const loadLibrary = async () => {
    try {
      const db = await openLibraryDb();
      const tx = db.transaction(IDB_STORE, 'readonly');
      const store = tx.objectStore(IDB_STORE);
      const req = store.getAll();
      const rows = await new Promise((resolve, reject) => { req.onsuccess = () => resolve(req.result || []); req.onerror = () => reject(req.error); });
      libraryTracks = rows;
      db.close();
    } catch (_) {
      try { libraryTracks = JSON.parse(localStorage.getItem(LIBRARY_META_KEY) || '[]'); } catch (_) { libraryTracks = []; }
    }
    dispatchLibraryUpdate();
  };

  const persistLibrary = async () => {
    localStorage.setItem(LIBRARY_META_KEY, JSON.stringify(libraryTracks));
    try {
      const db = await openLibraryDb();
      const tx = db.transaction(IDB_STORE, 'readwrite');
      const store = tx.objectStore(IDB_STORE);
      store.clear();
      libraryTracks.forEach((track) => store.put(track));
      db.close();
    } catch (_) {}
  };

  const currentTrack = () => state.queue[state.currentIndex] || null;
  const getOverlayEnabled = () => { const raw = localStorage.getItem(OVERLAY_KEY); return raw === null ? true : raw === '1'; };
  const fmt = (s) => !isFinite(s) || s < 0 ? '0:00' : `${Math.floor(s / 60)}:${Math.floor(s % 60).toString().padStart(2, '0')}`;

  const resolveTrackUrl = (track) => {
    if (!track) return '';
    if (track.url) return track.url;
    return runtimeFileMap.get(track.id) || '';
  };

  const persist = () => {
    const safeIndex = state.currentIndex >= 0 && state.currentIndex < state.queue.length ? state.currentIndex : -1;
    localStorage.setItem(STORAGE_KEY, JSON.stringify({
      queue: state.queue, currentIndex: safeIndex, currentTime: audio.currentTime || state.currentTime || 0,
      paused: audio.paused, volume: audio.volume,
      playlists: state.playlists, activePlaylistId: state.activePlaylistId, library: state.library
    }));
  };

  const style = document.createElement('style');
  style.textContent = '#global-music-player-overlay{position:fixed;right:16px;bottom:16px;width:300px;max-width:calc(100vw - 24px);z-index:1100;background:#121726;color:#fff;border-radius:12px;padding:10px 12px;box-shadow:0 10px 30px rgba(0,0,0,.35)}#global-music-player-overlay.hidden{display:none}.gmp-title{font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:8px}.gmp-controls{display:flex;gap:6px;justify-content:center;margin-bottom:8px}.gmp-btn{border:none;background:#26324a;color:#fff;border-radius:999px;width:34px;height:34px}.gmp-btn-primary{background:#5a7dff}.gmp-seek,.gmp-volume{width:100%}.gmp-meta{display:flex;justify-content:space-between;align-items:center;gap:10px;font-size:12px;margin-top:6px}.gmp-volume-wrap{display:flex;align-items:center;gap:6px;min-width:110px}';
  document.head.appendChild(style);

  const overlay = document.createElement('div');
  overlay.id = 'global-music-player-overlay';
  overlay.innerHTML = '<div class="gmp-title">No track selected</div><div class="gmp-controls"><button type="button" class="gmp-btn" data-act="prev"><i class="fas fa-backward"></i></button><button type="button" class="gmp-btn gmp-btn-primary" data-act="toggle"><i class="fas fa-play"></i></button><button type="button" class="gmp-btn" data-act="next"><i class="fas fa-forward"></i></button></div><input class="gmp-seek" type="range" min="0" max="100" step="0.1" value="0"/><div class="gmp-meta"><span class="gmp-time">0:00 / 0:00</span><div class="gmp-volume-wrap"><i class="fas fa-volume-up"></i><input class="gmp-volume" type="range" min="0" max="1" step="0.01" value="' + state.volume + '"/></div></div>';
  document.body.appendChild(overlay);

  const el = { title: overlay.querySelector('.gmp-title'), playPauseBtn: overlay.querySelector('[data-act="toggle"]'), seek: overlay.querySelector('.gmp-seek'), time: overlay.querySelector('.gmp-time'), volume: overlay.querySelector('.gmp-volume') };

  const updateUI = () => {
    const track = currentTrack();
    el.title.textContent = track ? (track.title || 'Untitled track') : 'No track selected';
    el.playPauseBtn.innerHTML = '<i class="fas ' + (audio.paused ? 'fa-play' : 'fa-pause') + '"></i>';
    const dur = audio.duration || 0; const cur = audio.currentTime || 0;
    el.seek.value = dur > 0 ? ((cur / dur) * 100) : 0;
    el.time.textContent = `${fmt(cur)} / ${fmt(dur)}`;
    el.volume.value = audio.volume;
    overlay.classList.toggle('hidden', !getOverlayEnabled());
  };

  const loadTrackAt = (index, autoplay) => {
    if (index < 0 || index >= state.queue.length) return;
    state.currentIndex = index;
    const track = currentTrack();
    const url = resolveTrackUrl(track);
    if (!url) return;
    if (audio.src !== url) audio.src = url;
    if (autoplay) audio.play().catch(() => {});
    updateUI(); persist();
  };

  const dispatchLibraryUpdate = () => document.dispatchEvent(new CustomEvent('studysmart:library-updated'));
  const dispatchPlaylistUpdate = () => document.dispatchEvent(new CustomEvent('studysmart:playlist-updated'));

  const parseTrackMeta = (file, path) => {
    const raw = (file.name || 'Track').replace(/\.[^/.]+$/, '');
    const pieces = raw.split(' - ').map((p) => p.trim());
    return {
      id: uid(),
      title: pieces[0] || raw,
      artist: pieces[1] || 'Unknown artist',
      album: pieces[2] || 'Local Files',
      folderPath: path || '',
      duration: 0,
      dateAdded: Date.now(),
      source: 'device'
    };
  };

  const filterAndSortLibrary = () => {
    const q = norm(state.library.query).toLowerCase();
    const result = libraryTracks.filter((t) => {
      if (!q) return true;
      return [t.title, t.artist, t.album].some((v) => norm(v).toLowerCase().includes(q));
    });
    result.sort((a, b) => {
      if (state.library.sortBy === 'date') return (b.dateAdded || 0) - (a.dateAdded || 0);
      if (state.library.sortBy === 'duration') return (b.duration || 0) - (a.duration || 0);
      return norm(a.title).localeCompare(norm(b.title));
    });
    return result;
  };

  const api = {
    enqueue: (track, playNow) => {
      if (!track) return;
      const idx = state.queue.findIndex((t) => t.id === track.id);
      if (idx === -1) { state.queue.push(track); if (state.currentIndex === -1) state.currentIndex = 0; }
      if (playNow) loadTrackAt(idx === -1 ? state.queue.length - 1 : idx, true);
      persist(); updateUI();
    },
    enqueueMany: (tracks) => {
      if (!Array.isArray(tracks)) return;
      tracks.forEach((t) => { if (t && t.id && !state.queue.some((q) => q.id === t.id)) state.queue.push(t); });
      if (state.currentIndex === -1 && state.queue.length) state.currentIndex = 0;
      persist(); updateUI();
    },
    play: () => { audio.play().catch(() => {}); persist(); updateUI(); },
    pause: () => { audio.pause(); persist(); updateUI(); },
    toggle: () => (audio.paused ? api.play() : api.pause()),
    next: () => { if (!state.queue.length) return; loadTrackAt((state.currentIndex + 1) % state.queue.length, true); },
    prev: () => { if (!state.queue.length) return; loadTrackAt((state.currentIndex - 1 + state.queue.length) % state.queue.length, true); },
    seek: (percent) => { if (!audio.duration) return; audio.currentTime = (Math.max(0, Math.min(100, percent)) / 100) * audio.duration; persist(); updateUI(); },
    setVolume: (v) => { audio.volume = Math.max(0, Math.min(1, v)); persist(); updateUI(); },
    setOverlayEnabled: (enabled) => { localStorage.setItem(OVERLAY_KEY, enabled ? '1' : '0'); updateUI(); },
    isOverlayEnabled: getOverlayEnabled,

    indexDeviceFiles: async (files) => {
      if (!Array.isArray(files)) return;
      const indexed = files.filter((f) => f && f.type && f.type.startsWith('audio/')).map((file) => {
        const folderPath = file.webkitRelativePath ? file.webkitRelativePath.split('/').slice(0, -1).join('/') : '';
        const track = parseTrackMeta(file, folderPath);
        runtimeFileMap.set(track.id, URL.createObjectURL(file));
        return track;
      });
      libraryTracks = [...libraryTracks, ...indexed];
      await persistLibrary();
      dispatchLibraryUpdate();
    },
    indexFromDirectoryHandle: async (dirHandle) => {
      const files = [];
      const walk = async (handle, path) => {
        for await (const entry of handle.values()) {
          if (entry.kind === 'directory') await walk(entry, path ? `${path}/${entry.name}` : entry.name);
          if (entry.kind === 'file') {
            const file = await entry.getFile();
            if (file.type && file.type.startsWith('audio/')) {
              Object.defineProperty(file, 'webkitRelativePath', { value: path ? `${path}/${file.name}` : file.name, configurable: true });
              files.push(file);
            }
          }
        }
      };
      await walk(dirHandle, '');
      await api.indexDeviceFiles(files);
    },
    getLibraryTracks: () => {
      const tracks = filterAndSortLibrary();
      if (!state.library.groupByFolder) return tracks;
      return tracks.sort((a, b) => norm(a.folderPath).localeCompare(norm(b.folderPath)) || norm(a.title).localeCompare(norm(b.title)));
    },
    findLibraryTrack: (trackId) => libraryTracks.find((t) => String(t.id) === String(trackId)) || null,
    setLibraryFilter: (query) => { state.library.query = query || ''; persist(); dispatchLibraryUpdate(); },
    setLibrarySort: (sortBy) => { state.library.sortBy = ['name', 'date', 'duration'].includes(sortBy) ? sortBy : 'name'; persist(); dispatchLibraryUpdate(); },
    setFolderGrouping: (enabled) => { state.library.groupByFolder = !!enabled; persist(); dispatchLibraryUpdate(); },

    getPlaylists: () => state.playlists,
    getActivePlaylist: () => state.playlists.find((p) => p.id === state.activePlaylistId) || null,
    setActivePlaylist: (playlistId) => { state.activePlaylistId = playlistId; persist(); dispatchPlaylistUpdate(); },
    createPlaylist: (name) => {
      const playlist = { id: uid(), name: norm(name) || 'Untitled Playlist', tracks: [] };
      state.playlists.push(playlist);
      state.activePlaylistId = playlist.id;
      persist(); dispatchPlaylistUpdate();
      return playlist;
    },
    renamePlaylist: (playlistId, name) => {
      const p = state.playlists.find((x) => x.id === playlistId); if (!p) return;
      p.name = norm(name) || p.name; persist(); dispatchPlaylistUpdate();
    },
    deletePlaylist: (playlistId) => {
      state.playlists = state.playlists.filter((p) => p.id !== playlistId);
      if (state.activePlaylistId === playlistId) state.activePlaylistId = state.playlists[0] ? state.playlists[0].id : null;
      persist(); dispatchPlaylistUpdate();
    },
    addTrackToPlaylist: (playlistId, trackId) => {
      const p = state.playlists.find((x) => x.id === playlistId); if (!p) return;
      if (!p.tracks.includes(trackId)) p.tracks.push(trackId);
      persist(); dispatchPlaylistUpdate();
    },
    removeTrackFromPlaylist: (playlistId, trackId) => {
      const p = state.playlists.find((x) => x.id === playlistId); if (!p) return;
      p.tracks = p.tracks.filter((id) => String(id) !== String(trackId));
      persist(); dispatchPlaylistUpdate();
    }
  };

  overlay.addEventListener('click', (e) => {
    const node = e.target.closest('[data-act]'); if (!node) return;
    const act = node.getAttribute('data-act');
    if (act === 'toggle') api.toggle(); if (act === 'next') api.next(); if (act === 'prev') api.prev();
  });
  el.seek.addEventListener('input', () => api.seek(parseFloat(el.seek.value)));
  el.volume.addEventListener('input', () => api.setVolume(parseFloat(el.volume.value)));

  audio.addEventListener('timeupdate', () => { state.currentTime = audio.currentTime || 0; updateUI(); persist(); });
  audio.addEventListener('play', updateUI);
  audio.addEventListener('pause', () => { state.currentTime = audio.currentTime || 0; updateUI(); persist(); });
  audio.addEventListener('ended', () => api.next());

  window.StudySmartMusicPlayer = api;
  loadLibrary();

  if (state.currentIndex >= 0 && state.currentIndex < state.queue.length) loadTrackAt(state.currentIndex, !state.paused);
  else updateUI();
})();
