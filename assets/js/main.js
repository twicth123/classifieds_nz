// Switch main ad image when a thumbnail is clicked
document.addEventListener('click', function (e) {
  const thumb = e.target.closest('.thumb-strip img');
  if (thumb) {
    const main = document.querySelector('.main-ad-image');
    if (main) main.src = thumb.dataset.full;
    document.querySelectorAll('.thumb-strip img').forEach(i => i.classList.remove('active'));
    thumb.classList.add('active');
  }
});

// Toggle favorite via AJAX
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.favorite-btn');
  if (!btn) return;
  e.preventDefault();
  const adId = btn.dataset.adId;
  fetch('toggle_favorite.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'ad_id=' + encodeURIComponent(adId) + '&csrf_token=' + encodeURIComponent(btn.dataset.csrf)
  })
  .then(r => r.json())
  .then(data => {
    if (data.status === 'unauthenticated') {
      window.location.href = 'login.php';
      return;
    }
    const isDetail = btn.classList.contains('detail-fav');
    if (data.favorited) {
      btn.classList.add('text-danger');
      btn.innerHTML = isDetail ? '<i class="bi bi-heart-fill me-2"></i>Saved to Favorites' : '<i class="bi bi-heart-fill"></i>';
    } else {
      btn.classList.remove('text-danger');
      btn.innerHTML = isDetail ? '<i class="bi bi-heart me-2"></i>Save to Favorites' : '<i class="bi bi-heart"></i>';
      if (btn.dataset.removeOnUnfav === '1') {
        const card = btn.closest('.ad-card-wrapper');
        if (card) card.remove();
      }
    }
  })
  .catch(() => alert('Something went wrong. Please try again.'));
});

// Live char counter for description
document.addEventListener('DOMContentLoaded', function () {
  const desc = document.getElementById('description');
  const counter = document.getElementById('descCounter');
  if (desc && counter) {
    const update = () => counter.textContent = desc.value.length + ' characters';
    desc.addEventListener('input', update);
    update();
  }
});
