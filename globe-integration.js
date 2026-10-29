/**
 * PickMyWaste - 3D Interactive Globe.gl Controller
 * Highlights international operations (India, Middle East, Europe)
 * Optimized with IntersectionObserver and ResizeObserver for zero performance drag.
 */
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('3d-globe-container');
    if (!container) return;

    // 1. O(1) Hash Map for fast country lookup
    const regionColorMap = {};
    ['IND'].forEach(c => regionColorMap[c] = '#2e7d32'); // Green - India
    ['SAU', 'ARE', 'QAT', 'OMN', 'KWT', 'BHR', 'JOR', 'LBN', 'EGY', 'IRQ', 'ISR', 'YEM'].forEach(c => regionColorMap[c] = '#00a896'); // Teal - Middle East
    ['DEU', 'FRA', 'GBR', 'ITA', 'ESP', 'PRT', 'NLD', 'BEL', 'CHE', 'AUT', 'POL', 'DNK', 'SWE', 'NOR', 'FIN', 'IRL', 'GRC', 'CZE', 'ROU', 'HUN'].forEach(c => regionColorMap[c] = '#0288d1'); // Blue - Europe

    // Cache altitude to avoid layout checks inside animation frames
    let currentAltitude = 1.7;

    // 2. Container sizing by screen size
    function adjustContainerSize() {
        const screenWidth = window.innerWidth;
        if (screenWidth < 768) {
            container.style.height = '280px';
            container.style.maxWidth = '280px';
            currentAltitude = 1.8;
        } else if (screenWidth <= 1024) {
            container.style.height = '360px';
            container.style.maxWidth = '360px';
            currentAltitude = 1.9;
        } else {
            container.style.height = '450px';
            container.style.maxWidth = '450px';
            currentAltitude = 1.7;
        }
    }

    adjustContainerSize();

    // 3. Initialize globe
    const world = Globe()(container)
        .backgroundColor('rgba(0,0,0,0)')
        .showAtmosphere(true)
        .atmosphereColor('#80d8ff')
        .atmosphereAltitude(0.12)
        .polygonAltitude(0.01)
        .polygonStrokeColor(() => '#ffffff')
        .polygonCapColor(d => regionColorMap[d.properties.ISO_A3] || '#cbd5e1')
        .polygonSideColor(() => 'rgba(0, 0, 0, 0.05)')
        .htmlElementsData([
            { lat: 20.5937, lng: 78.9629, name: 'India', color: '#2e7d32' },
            { lat: 25.2048, lng: 55.2708, name: 'Middle East', color: '#00a896' },
            { lat: 50.1109, lng: 8.6821, name: 'Europe', color: '#0288d1' }
        ])
        .htmlElement(d => {
            const el = document.createElement('div');
            el.innerHTML = `
                <div style="
                    background: rgba(255, 255, 255, 0.95);
                    color: #0a2540;
                    padding: 4px 12px;
                    border-radius: 20px;
                    font-family: Arial, sans-serif;
                    font-size: 12px;
                    font-weight: bold;
                    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
                    border: 2px solid ${d.color};
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    transform: translate(-50%, -120%);
                    white-space: nowrap;
                ">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background-color: ${d.color}; display: inline-block;"></span>
                    ${d.name}
                </div>
            `;
            return el;
        });

    // Ocean color
    world.globeMaterial().color.set('#09274C');

    // 4. Fetch boundary data
    fetch('https://raw.githubusercontent.com/vasturiano/globe.gl/master/example/datasets/ne_110m_admin_0_countries.geojson')
        .then(res => res.json())
        .then(countries => {
            world.polygonsData(countries.features);
        });

    // 5. Controls
    world.controls().autoRotate = false;
    world.controls().enableZoom = false;

    // 6. Camera animation loop (pauses when off-screen)
    let currentLng = 35;
    let sweepDirection = 1;
    let animFrameId = null;
    let isVisible = true;

    function animateSweep() {
        if (!isVisible) return;

        currentLng += 0.08 * sweepDirection;
        if (currentLng > 75) sweepDirection = -1;
        if (currentLng < -5) sweepDirection = 1;

        world.pointOfView({ lat: 28, lng: currentLng, altitude: currentAltitude });
        animFrameId = requestAnimationFrame(animateSweep);
    }

    // IntersectionObserver: stop/start animation on scroll
    const visibilityObserver = new IntersectionObserver(([entry]) => {
        isVisible = entry.isIntersecting;
        if (isVisible) {
            cancelAnimationFrame(animFrameId);
            animateSweep();
        }
    }, { threshold: 0.1 });

    visibilityObserver.observe(container);

    // 7. Auto-resize listener
    let resizeTimer;
    new ResizeObserver(() => {
        cancelAnimationFrame(resizeTimer);
        resizeTimer = requestAnimationFrame(() => {
            adjustContainerSize();
            const width = container.clientWidth;
            const height = container.clientHeight;
            if (width > 0 && height > 0) {
                world.width(width).height(height);
            }
        });
    }).observe(container);
});
