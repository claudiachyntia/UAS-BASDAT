// navbar scrolled
const navE1 = document.querySelector('.navbar');

window.addEventListener('scroll', () => {
    if (window.scrollY >= 56) {
        navE1.classList.add('navbar-scrolled');
    } else {
        navE1.classList.remove('navbar-scrolled');
    }
});

// Load More functionality
let loadMoreBtn = document.getElementById('loadMoreBtn');
let discoverCards = document.querySelectorAll('.discover__card');
let visibleCards = 8; 

// Function to show discover cards
function showDiscoverCards() {
    discoverCards.forEach((card, index) => {
        card.style.display = 'none';
    });

    for (let i = 0; i < visibleCards; i++) {
        if (discoverCards[i]) {
            discoverCards[i].style.display = 'block';
        }
    }

    if (visibleCards >= discoverCards.length) {
        loadMoreBtn.style.display = 'none';
    }
}

showDiscoverCards();

loadMoreBtn.addEventListener('click', () => {
    visibleCards += 8; 
    showDiscoverCards(); 
});