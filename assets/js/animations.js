/**
 * Atlas Menu 滚动动画脚本
 * 实现元素进入视口时的淡入动画效果
 */

// 滚动动画观察器
class ScrollAnimator {
    constructor() {
        this.observer = null;
        this.init();
    }

    init() {
        // 创建 Intersection Observer
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    // 动画完成后停止观察
                    this.observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        // 观察所有需要动画的元素
        this.observeElements();
    }

    observeElements() {
        const animatedElements = document.querySelectorAll('.fade-in-up, .fade-in-left, .fade-in-right, .fade-in-scale');
        animatedElements.forEach(el => {
            this.observer.observe(el);
        });
    }
}

// 数字计数器动画
class CounterAnimator {
    constructor() {
        this.counters = document.querySelectorAll('.counter');
        this.observer = null;
        this.init();
    }

    init() {
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateCounter(entry.target);
                    this.observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.5
        });

        this.counters.forEach(counter => {
            this.observer.observe(counter);
        });
    }

    animateCounter(element) {
        const target = parseInt(element.getAttribute('data-target'));
        const duration = 2000; // 2秒
        const step = target / (duration / 16); // 每16ms的增量
        let current = 0;

        const timer = setInterval(() => {
            current += step;
            if (current >= target) {
                element.textContent = target.toLocaleString();
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current).toLocaleString();
            }
        }, 16);
    }
}

// 页面加载完成后初始化
document.addEventListener('DOMContentLoaded', () => {
    new ScrollAnimator();
    new CounterAnimator();
});
