const BASE = document.documentElement.dataset.base || "";

async function loadComponent(selector, url) {
  const targets = document.querySelectorAll(selector);
  if (!targets.length) return false;
  try {
    const res = await fetch(url, { cache: "no-store" });
    if (!res.ok) throw new Error(`HTTP ${res.status} pada ${url}`);
    const raw = await res.text();
    const html = raw.replaceAll("{{BASE}}", BASE);
    targets.forEach((el) => (el.innerHTML = html));
    return true;
  } catch (err) {
    console.error(err);
    targets.forEach((el) => {
      el.innerHTML = `<p class="p-4 text-sm text-brick">Gagal memuat ${url}. Buka situs menggunakan server lokal.</p>`;
    });
    return false;
  }
}

function setActiveNavLink() {
  const page = document.body.dataset.page;
  if (!page) return;
  document.querySelectorAll(".nav-link[data-page]").forEach((link) => {
    if (link.dataset.page === page) {
      link.classList.add("text-brick", "font-bold");
    }
  });
}

function initLiveDate() {
  const el = document.getElementById("live-date");
  if (!el) return;
  el.textContent = new Date().toLocaleDateString("id-ID", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

function initCopyrightYear() {
  const el = document.getElementById("copyright-year");
  if (el) el.textContent = new Date().getFullYear();
}

function ensureMobileMenu() {
  let menu = document.getElementById("mobile-menu");
  if (menu) return menu;

  const header = document.querySelector("#site-navbar header");
  const desktopNav = header && header.querySelector("nav");
  if (!header || !desktopNav) return null;

  if (getComputedStyle(header).position === "static") header.style.position = "relative";

  menu = document.createElement("div");
  menu.id = "mobile-menu";
  menu.style.display = "none";
  menu.className =
    "absolute inset-x-0 top-full max-h-[calc(100vh-5rem)] overflow-y-auto border-t border-b border-ink/10 bg-paper px-6 py-3 shadow-lg";

  const nav = document.createElement("nav");
  nav.className = "flex flex-col text-sm font-medium text-ink-soft";
  desktopNav.querySelectorAll("a[href]").forEach((a) => {
    const link = document.createElement("a");
    link.href = a.getAttribute("href");
    link.textContent = a.textContent.trim();
    link.className = "py-2.5 border-b border-ink/5 hover:text-brick font-semibold";
    nav.appendChild(link);
  });
  menu.appendChild(nav);
  header.appendChild(menu);
  return menu;
}

function setMobileMenu(open) {
  const menu = ensureMobileMenu();
  const btn = document.getElementById("menu-toggle");
  if (!menu || !btn) return;
  menu.style.display = open ? "block" : "none";
  btn.setAttribute("aria-expanded", String(open));
  const iconOpen = document.getElementById("icon-open");
  const iconClose = document.getElementById("icon-close");
  if (iconOpen) iconOpen.style.display = open ? "none" : "block";
  if (iconClose) iconClose.style.display = open ? "block" : "none";
}

document.addEventListener("click", (e) => {
  if (e.target.closest("#menu-toggle")) {
    const menu = ensureMobileMenu();
    if (!menu) {
      console.warn("Menu mobile tidak bisa dibuat: <header> atau <nav> tidak ditemukan di navbar.");
      return;
    }
    setMobileMenu(getComputedStyle(menu).display === "none");
  } else if (e.target.closest("#mobile-menu a") || !e.target.closest("#site-navbar")) {
    setMobileMenu(false); // tutup saat link diklik atau klik di luar navbar
  }
});

window.addEventListener("resize", () => {
  if (window.innerWidth >= 1024) setMobileMenu(false);
});

async function bootstrap() {
  await Promise.all([
    loadComponent("#site-navbar", `${BASE}components/navbar.html`),
    loadComponent("#site-footer", `${BASE}components/footer.html`),
  ]);

  setActiveNavLink();
  initLiveDate();
  initCopyrightYear();

  document.dispatchEvent(new CustomEvent("components:ready"));
}

document.addEventListener("DOMContentLoaded", bootstrap);