/**
 * Atlas Menu 主交互脚本
 * 处理导航、FAQ手风琴、平滑滚动等交互功能
 */

// 移动端菜单切换
class MobileMenu {
    constructor() {
        this.menuBtn = document.getElementById('mobile-menu-btn');
        this.mobileMenu = document.getElementById('mobile-menu');
        this.isOpen = false;
        this.init();
    }

    init() {
        if (this.menuBtn && this.mobileMenu) {
            this.menuBtn.addEventListener('click', () => this.toggleMenu());
            
            // 点击遮罩层关闭菜单
            const overlay = this.mobileMenu.querySelector('.mobile-menu-overlay');
            if (overlay) {
                overlay.addEventListener('click', () => this.closeMenu());
            }
            
            // 点击菜单项后关闭菜单
            const menuLinks = this.mobileMenu.querySelectorAll('a');
            menuLinks.forEach(link => {
                link.addEventListener('click', () => this.closeMenu());
            });
        }
    }

    toggleMenu() {
        this.isOpen = !this.isOpen;
        
        if (this.isOpen) {
            this.mobileMenu.classList.add('active');
        } else {
            this.mobileMenu.classList.remove('active');
        }
        
        // 更新按钮图标
        const icon = this.menuBtn.querySelector('svg');
        if (this.isOpen) {
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
        } else {
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>';
        }
    }

    closeMenu() {
        if (this.isOpen) {
            this.isOpen = false;
            this.mobileMenu.classList.remove('active');
            const icon = this.menuBtn.querySelector('svg');
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>';
        }
    }
}

// FAQ手风琴
class FAQAccordion {
    constructor() {
        this.faqItems = document.querySelectorAll('.faq-item');
        this.init();
    }

    init() {
        this.faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            const answer = item.querySelector('.faq-answer');
            const icon = item.querySelector('.faq-icon');

            question.addEventListener('click', () => {
                this.toggleItem(item, answer, icon);
            });
        });
    }

    toggleItem(item, answer, icon) {
        const isOpen = answer.classList.contains('active');

        // 关闭所有其他项
        this.faqItems.forEach(otherItem => {
            if (otherItem !== item) {
                const otherAnswer = otherItem.querySelector('.faq-answer');
                const otherIcon = otherItem.querySelector('.faq-icon');
                otherAnswer.classList.remove('active');
                otherIcon.classList.remove('active');
                otherAnswer.style.maxHeight = '0';
            }
        });

        // 切换当前项
        if (isOpen) {
            answer.classList.remove('active');
            icon.classList.remove('active');
            answer.style.maxHeight = '0';
        } else {
            answer.classList.add('active');
            icon.classList.add('active');
            answer.style.maxHeight = answer.scrollHeight + 'px';
        }
    }
}

// 平滑滚动
class SmoothScroll {
    constructor() {
        this.init();
    }

    init() {
        // 处理所有锚点链接
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        anchorLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if (href !== '#' && href.length > 1) {
                    const target = document.querySelector(href);
                    if (target) {
                        e.preventDefault();
                        this.scrollTo(target);
                    }
                }
            });
        });
    }

    scrollTo(element) {
        const offset = 80; // 导航栏高度
        const elementPosition = element.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - offset;

        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
    }
}

// 导航栏滚动效果
class NavbarScroll {
    constructor() {
        this.navbar = document.querySelector('nav');
        this.init();
    }

    init() {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                this.navbar.classList.add('shadow-lg');
                this.navbar.style.backgroundColor = 'rgba(10, 10, 15, 0.95)';
            } else {
                this.navbar.classList.remove('shadow-lg');
                this.navbar.style.backgroundColor = 'rgba(10, 10, 15, 0.8)';
            }
        });
    }
}

// 页面加载完成后初始化所有功能
document.addEventListener('DOMContentLoaded', () => {
    new MobileMenu();
    new FAQAccordion();
    new SmoothScroll();
    new NavbarScroll();
});
