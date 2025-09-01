
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        btn.textContent = "🙈";
    } else {
        input.type = "password";
        btn.textContent = "👁";
    }
}


document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.header_gorny');
    const pasekPrzyciskow = document.getElementById('pasek_przyciskow');
    const pasekInformujacy = document.getElementById('pasek_informujący');
    const logoContainer = document.getElementById('logo_male_container');
    const nazwa = document.querySelector('.nazwa');
    const footer = document.querySelector('.footer_dolny');
    const rangaLogoMini = document.getElementById('rangaLogoMini');
    const hiddenRangaImg = document.getElementById('hidden-ranga');
    const nickUzytkownika = document.getElementById('nickUzytkownika');
    const plecSelect = document.getElementById('plecInput');


    let isFixed = false;

    function checkScrollPosition() {
        if (!(header && pasekPrzyciskow && pasekInformujacy && footer)) return;

        const scrollY = window.scrollY || window.pageYOffset;
        const offset = 100;
        const headerRect = header.getBoundingClientRect();
        const footerRect = footer.getBoundingClientRect();
        const windowHeight = window.innerHeight;


        if (scrollY > offset) {
            pasekPrzyciskow.classList.add('fixed');
            pasekInformujacy.classList.add('fixed');
        } else {
            pasekPrzyciskow.classList.remove('fixed');
            pasekInformujacy.classList.remove('fixed');
        }


        if (headerRect.bottom <= 0 && !isFixed) {
            pasekPrzyciskow.classList.add('show-logo');
            if (nazwa) nazwa.style.opacity = '0';
            isFixed = true;
        } else if (headerRect.bottom > 0 && isFixed) {
            pasekPrzyciskow.classList.remove('show-logo');
            if (nazwa) nazwa.style.opacity = '1';
            isFixed = false;
        }


        if (footerRect.top < windowHeight) {
            pasekInformujacy.classList.remove('fixed-bottom');
            pasekInformujacy.classList.add('absolute-bottom');
            pasekInformujacy.style.top = (window.scrollY + footerRect.top - pasekInformujacy.offsetHeight) + 'px';
        } else {
            pasekInformujacy.classList.remove('absolute-bottom');
            pasekInformujacy.classList.add('fixed-bottom');
            pasekInformujacy.style.top = '';
        }
    }

    function updateInformacyjnyPasek() {
        if (!(footer && rangaLogoMini && hiddenRangaImg && nickUzytkownika)) return;

        const footerRect = footer.getBoundingClientRect();
        const windowHeight = window.innerHeight;
        const footerWidoczny = footerRect.top < windowHeight && footerRect.bottom > 0;

        if (!footerWidoczny) {
            if (rangaLogoMini.childElementCount === 0 && hiddenRangaImg) {
                const clonedImg = hiddenRangaImg.cloneNode();
                clonedImg.style.display = 'inline-block';
                clonedImg.style.height = '40px';
                clonedImg.style.marginRight = '10px';
                rangaLogoMini.appendChild(clonedImg);
            }
            rangaLogoMini.style.display = 'flex';
            nickUzytkownika.style.display = 'block';
        } else {
            rangaLogoMini.innerHTML = '';
            rangaLogoMini.style.display = 'none';
            nickUzytkownika.style.display = 'none';
        }
    }

    function updatePasekPosition() {
        const footerRect = footer.getBoundingClientRect();
        const windowHeight = window.innerHeight;
        const pasekHeight = pasekInformujacy.offsetHeight;

        if (footerRect.top <= windowHeight && footerRect.top > pasekHeight) {
            pasekInformujacy.style.position = 'absolute';
            pasekInformujacy.style.bottom = (document.body.offsetHeight - footer.offsetTop) + 'px';
        } else if (footerRect.top <= pasekHeight) {
            pasekInformujacy.style.position = 'absolute';
            pasekInformujacy.style.bottom = (document.body.offsetHeight - footer.offsetTop + 5) + 'px';
        } else {
            pasekInformujacy.style.position = 'fixed';
            pasekInformujacy.style.bottom = '0';
        }
    }

    function updateSelectBackground() {
        if (!plecSelect) return;
        plecSelect.classList.toggle('inny', plecSelect.value === 'inny');
    }

    window.addEventListener('scroll', () => {
        checkScrollPosition();
        updateInformacyjnyPasek();
        updatePasekPosition();
    });

    window.addEventListener('resize', () => {
        checkScrollPosition();
        updateInformacyjnyPasek();
        updatePasekPosition();
    });

    if (plecSelect) {
        plecSelect.addEventListener('change', updateSelectBackground);
        updateSelectBackground();
    }

    checkScrollPosition();
    updateInformacyjnyPasek();
    updatePasekPosition();
});
