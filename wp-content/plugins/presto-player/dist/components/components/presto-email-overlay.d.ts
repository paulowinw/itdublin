import type { Components, JSX } from "../types/components";

interface PrestoEmailOverlay extends Components.PrestoEmailOverlay, HTMLElement {}
export const PrestoEmailOverlay: {
    prototype: PrestoEmailOverlay;
    new (): PrestoEmailOverlay;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
