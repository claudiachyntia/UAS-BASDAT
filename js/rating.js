let starContainer = document.querySelectorAll(".star-container"); //rating
const submitButton = document.querySelector("#submit");
const message = document.querySelector("#message");
const submitSection = document.querySelector("#submit-section");
const dynamicRating = document.querySelector("#dynamic-rating"); 

let events = {
    mouse: {
        over: "click",
    },
    touch: {
        over: "touchstart",
    },
};

let deviceType = "";
let selectedRating = 0;

const isTouchDevice = () => {
    try {
        document.createEvent("TouchEvent");
        deviceType = "touch";
        return true;
    } catch (e) {
        deviceType = "mouse";
        return false;
    }
};

isTouchDevice();

starContainer.forEach((element, index) => {
    element.addEventListener(events[deviceType].over, () => {
        submitButton.disabled = false;
        ratingUpdate(0, starContainer.length - 1, false);
        ratingUpdate(0, index, true);
        selectedRating = index + 1;
        updateRatingDisplay(averageRating);
    });
});

const ratingUpdate = (start, end, active) => {
    for (let i = start; i <= end; i++) {
        if (active) {
            starContainer[i].classList.add("inactive");
            starContainer[i].classList.remove("active");
            starContainer[i].firstElementChild.className = "fa-star fa-solid";
        } else {
            starContainer[i].classList.remove("inactive");
            starContainer[i].classList.add("active");
            starContainer[i].firstElementChild.className = "fa-star fa-regular";
        }
    }

    let activeElements = document.getElementsByClassName("inactive");
    if (activeElements.length > 0) {
        switch (activeElements.length) {
            case 1:
                message.innerText = "Terrible";
                break;
            case 2:
                message.innerText = "Bad";
                break;
            case 3:
                message.innerText = "Good";
                break;
            case 4:
                message.innerText = "Satisfied";
                break;
            case 5:
                message.innerText = "Excellent";
                break;
        }
    } else {
        message.innerText = "Rate Your Experience";
    }
};


submitButton.addEventListener("click", () => {
    if (selectedRating > 0) {
        if (!isLoggedIn) {
            // Redirect user to login page if not logged in
            window.location.href = 'login.php';
            return; // Stop further execution
        }
        // Ambil userId dan tourismId dari atribut tombol
        var userId = submitButton.getAttribute('data-user-id');
        var tourismId = submitButton.getAttribute('data-tourism-id');
        
        // Cek apakah userId dan tourismId valid
        if (!userId || !tourismId) {
            console.error("User ID atau Tourism ID tidak valid.");
            return;
        }
        const updateRatingDisplay = (rating) => {
            let starsHTML = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= rating) {
                    starsHTML += '★';  
                } else {
                    starsHTML += '☆';  
                }
            }
            dynamicRating.innerHTML = `${starsHTML} (${rating}/5)`; 
        };

        // AJAX request ke submit_rating.php
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../submit_rating.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const newAverageRating = parseFloat(xhr.responseText);
                updateRatingDisplay(averageRating);
                console.log("Rating saved:", xhr.responseText);
                submitButton.style.display = 'none'; // Sembunyikan tombol submit
                // Nonaktifkan semua bintang
                starContainer.forEach((element) => {
                    element.style.pointerEvents = 'none'; // Nonaktifkan interaksi
                });
                
                // Tampilkan pesan sukses
                message.innerText = "Thank you for your feedback!";
                
                // Tampilkan rating yang telah dipilih
                starContainer.forEach((element, index) => {
                    if (index < selectedRating) {
                        element.firstElementChild.className = 'fa-star fa-solid'; // Bintang terisi
                    } else {
                        element.firstElementChild.className = 'fa-star fa-regular'; // Bintang kosong
                    }
                });
            }
        };
        xhr.send(`rating=${selectedRating}&user_id=${userId}&tourism_id=${tourismId}`);
    } else {
        message.innerText = "Please select a rating before submitting.";
    }
});

window.onload = () => {
    submitButton.disabled = true;
    submitSection.classList.add("hide");
    message.innerText = "Rate Your Experience";
};