// Carousel sederhana: tombol panah, titik, dan otomatis tiap 5 detik
(function () {
  const slides = document.querySelectorAll('.slide');
  const dotsBox = document.querySelector('.dots');
  let index = 0, timer;

  slides.forEach((_, i) => {
    const b = document.createElement('button');
    b.setAttribute('aria-label', 'Slide ' + (i + 1));
    b.addEventListener('click', () => { show(i); restart(); });
    dotsBox.appendChild(b);
  });
  const dots = dotsBox.querySelectorAll('button');

  function show(i) {
    index = (i + slides.length) % slides.length;
    slides.forEach((s, n) => s.classList.toggle('active', n === index));
    dots.forEach((d, n) => d.classList.toggle('active', n === index));
  }
  function restart() { clearInterval(timer); timer = setInterval(() => show(index + 1), 5000); }

  document.querySelector('.arrow.prev').addEventListener('click', () => { show(index - 1); restart(); });
  document.querySelector('.arrow.next').addEventListener('click', () => { show(index + 1); restart(); });

  show(0);
  restart();
})();
