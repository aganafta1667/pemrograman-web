// Logika pergantian Form Sign In dan Sign Up
document.addEventListener('DOMContentLoaded', function () {
  const signInSection = document.getElementById('signin-section');
  const signUpSection = document.getElementById('signup-section');
  
  const goToSignUpBtn = document.getElementById('go-to-signup');
  const goToSignInBtn = document.getElementById('go-to-signin');

  if (goToSignUpBtn) {
    goToSignUpBtn.addEventListener('click', function (e) {
      e.preventDefault();
      signInSection.classList.remove('active');
      signUpSection.classList.add('active');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  if (goToSignInBtn) {
    goToSignInBtn.addEventListener('click', function (e) {
      e.preventDefault();
      signUpSection.classList.remove('active');
      signInSection.classList.add('active');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // Validasi konfirmasi password pada Sign Up
  const signupForm = document.getElementById('signup-form');
  if (signupForm) {
    signupForm.addEventListener('submit', function (e) {
      const password = document.getElementById('signup-password');
      const passwordConfirm = document.getElementById('signup-password-confirm');
      const termsCheckbox = document.getElementById('terms-checkbox');

      if (password.value !== passwordConfirm.value) {
        e.preventDefault();
        alert('Password dan Konfirmasi Password tidak cocok!');
        passwordConfirm.focus();
        return;
      }

      if (!termsCheckbox.checked) {
        e.preventDefault();
        alert('Silakan setujui Terms and Conditions terlebih dahulu.');
        termsCheckbox.focus();
        return;
      }
    });
  }

  // Tombol setuju di dalam modal Terms & Conditions
  const modalAgreeBtn = document.getElementById('modal-agree-btn');
  if (modalAgreeBtn) {
    modalAgreeBtn.addEventListener('click', function () {
      const termsCheckbox = document.getElementById('terms-checkbox');
      if (termsCheckbox) {
        termsCheckbox.checked = true;
      }
    });
  }
});
