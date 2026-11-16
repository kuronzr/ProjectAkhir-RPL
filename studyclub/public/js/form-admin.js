(function () {
  const MAX_MODUL_SIZE = 10 * 1024 * 1024; // 10 MB
  const MAX_VIDEO_SIZE = 200 * 1024 * 1024; // 200 MB 

  const modulDrop = document.getElementById('modul-drop');
  const modulInput = document.getElementById('modul-input');
  const modulTitle = document.getElementById('modul-title');

  if (modulDrop && modulInput && modulTitle) {
    modulDrop.addEventListener('click', () => modulInput.click());
    modulInput.addEventListener('change', () => {
      const file = modulInput.files[0];
      if (!file) return;
      if (file.size > MAX_MODUL_SIZE) {
        alert('Ukuran file modul maksimal 10MB.');
        modulInput.value = '';
        modulTitle.textContent = 'Pilih file di sini';
        return;
      }
      modulTitle.textContent = file.name;
    });
  }

  const videoDrop = document.getElementById('video-drop');
  const videoInput = document.getElementById('video-input');
  const videoTitle = document.getElementById('video-title');

  if (videoDrop && videoInput && videoTitle) {
    videoDrop.addEventListener('click', () => videoInput.click());
    videoInput.addEventListener('change', () => {
      const file = videoInput.files[0];
      if (!file) return;
      if (file.size > MAX_VIDEO_SIZE) {
        alert('Ukuran file video maksimal 200MB.');
        videoInput.value = '';
        videoTitle.textContent = 'Pilih file di sini';
        return;
      }
      videoTitle.textContent = file.name;
    });
  }
})();