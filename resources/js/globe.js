/**
 * Vesper — Globe terrestre WebGL (librairie cobe).
 * Rendu professionnel : continents en points, marqueurs de villes, rotation continue.
 * https://github.com/shuding/cobe
 */
import createGlobe from 'cobe';

/**
 * Initialise le globe sur un <canvas>.
 * @param {HTMLCanvasElement} canvas
 * @returns {{ destroy: () => void } | null}
 */
export function initGlobe(canvas) {
    if (!canvas) {
        return null;
    }

    try {
        let phi = 0;

        const globe = createGlobe(canvas, {
            devicePixelRatio: 2,
            width: 1000,
            height: 1000,
            phi: 0,
            theta: 0.22,
            dark: 1,
            diffuse: 1.2,
            mapSamples: 16000,
            mapBrightness: 6,
            baseColor: [0.05, 0.55, 0.5],
            markerColor: [0.98, 0.72, 0.25],
            glowColor: [0.16, 0.72, 0.62],
            markers: [
                { location: [48.8566, 2.3522], size: 0.06 }, // Paris
                { location: [51.5074, -0.1278], size: 0.05 }, // Londres
                { location: [40.7128, -74.006], size: 0.06 }, // New York
                { location: [-4.3217, 15.3125], size: 0.08 }, // Kinshasa (RDC)
                { location: [0.4162, 9.4673], size: 0.05 }, // Libreville
                { location: [6.5244, 3.3792], size: 0.06 }, // Lagos
                { location: [-26.2041, 28.0473], size: 0.06 }, // Johannesburg
                { location: [35.6762, 139.6503], size: 0.06 }, // Tokyo
                { location: [19.076, 72.8777], size: 0.06 }, // Bombay
                { location: [1.3521, 103.8198], size: 0.05 }, // Singapour
                { location: [-33.8688, 151.2093], size: 0.05 }, // Sydney
                { location: [14.5995, 120.9842], size: 0.05 }, // Manille
            ],
            onRender: (state) => {
                state.phi = phi;
                phi += 0.004;
            },
        });

        return globe;
    } catch (error) {
        // WebGL indisponible ou erreur : on laisse la page fonctionner sans globe.
        console.error('Impossible d\'initialiser le globe :', error);
        return null;
    }
}
