function preserveIntWords() {
  const highlightSW = document.getElementById('highlightSW');
  const showOW = document.getElementById('showOW');
  const showTN = document.getElementById('showTN');

  const links = document.querySelectorAll('.planTable a');

  links.forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();

      const url = new URL(link.href);
      url.searchParams.set('highlightSW', highlightSW.checked);
      url.searchParams.set('showOW', showOW.checked);
      url.searchParams.set('showTN', showTN.checked);

      window.location.href = url.toString();
    });
  });
}

preserveIntWords();
