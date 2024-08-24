import type { Components, JSX } from "../types/components";

interface PrestoCtaOverlayUi extends Components.PrestoCtaOverlayUi, HTMLElement {}
export const PrestoCtaOverlayUi: {
    prototype: PrestoCtaOverlayUi;
    new (): PrestoCtaOverlayUi;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
