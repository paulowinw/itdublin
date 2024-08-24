import type { Components, JSX } from "../types/components";

interface PrestoDynamicOverlayUi extends Components.PrestoDynamicOverlayUi, HTMLElement {}
export const PrestoDynamicOverlayUi: {
    prototype: PrestoDynamicOverlayUi;
    new (): PrestoDynamicOverlayUi;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
