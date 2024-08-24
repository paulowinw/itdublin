import type { Components, JSX } from "../types/components";

interface PrestoDynamicOverlays extends Components.PrestoDynamicOverlays, HTMLElement {}
export const PrestoDynamicOverlays: {
    prototype: PrestoDynamicOverlays;
    new (): PrestoDynamicOverlays;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
