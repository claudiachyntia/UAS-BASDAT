const sign_in_btn = document.querySelector("#sign-in-btn");
const sign_up_btn = document.querySelector("#sign-up-btn");
const container = document.querySelector(".auth-page-container"); 

sign_up_btn.addEventListener("click", () => {
    container.classList.add("sign-up-mode");
});

sign_in_btn.addEventListener("click", () => {
    container.classList.remove("sign-up-mode");
});

function togglePassword(passwordId, iconId) {
    const passwordInput = document.getElementById(passwordId);
    const toggleIcon = document.getElementById(iconId);

    if (passwordInput.type === "password") {
        passwordInput.type = "text"; 
        toggleIcon.classList.remove("bi-eye"); 
        toggleIcon.classList.add("bi-eye-slash");
    } else {
        passwordInput.type = "password"; 
        toggleIcon.classList.remove("bi-eye-slash"); 
        toggleIcon.classList.add("bi-eye"); 
    }
}