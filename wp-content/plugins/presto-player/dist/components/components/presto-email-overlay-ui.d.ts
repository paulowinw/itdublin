import type { Components, JSX } from "../types/components";

interface PrestoEmailOverlayUi extends Components.PrestoEmailOverlayUi, HTMLElement {}
export const PrestoEmailOverlayUi: {
    prototype: PrestoEmailOverlayUi;
    new (): PrestoEmailOverlayUi;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
