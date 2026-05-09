document.addEventListener('DOMContentLoaded', () => {
    const backgroundImages = [
        'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=1920&q=80',
        'https://images.unsplash.com/photo-1578632767115-351597cf2477?auto=format&fit=crop&w=1920&q=80',
        'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1920&q=80'
    ];

    const sliderContainer = document.getElementById('bg-slider');
    if (sliderContainer) {
        let currentSlide = 0;

        backgroundImages.forEach((imgSrc, index) => {
            const slide = document.createElement('div');
            slide.classList.add('bg-slide');
            if (index === 0) slide.classList.add('active');
            slide.style.backgroundImage = `url('${imgSrc}')`;
            sliderContainer.appendChild(slide);
        });

        const slides = document.querySelectorAll('.bg-slide');
        setInterval(() => {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }, 10000);
    }

    const burger = document.getElementById('burger-mnu');
    const nav = document.querySelector('.nav-links');
    const navLinks = document.querySelectorAll('.nav-links li a');

    if (burger && nav) {
        burger.addEventListener('click', () => {
            nav.classList.toggle('nav-active');
            burger.classList.toggle('toggle');
        });

        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (nav.classList.contains('nav-active')) {
                    nav.classList.remove('nav-active');
                    burger.classList.remove('toggle');
                }
            });
        });
    }

    const header = document.getElementById('nav-bar');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.style.background = 'rgba(11, 11, 14, 0.98)';
                header.style.padding = '1rem 5%';
            } else {
                header.style.background = 'rgba(11, 11, 14, 0.8)';
                header.style.padding = '1.2rem 5%';
            }
        });
    }

    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.15
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('show');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.hidden').forEach((el) => observer.observe(el));
});
