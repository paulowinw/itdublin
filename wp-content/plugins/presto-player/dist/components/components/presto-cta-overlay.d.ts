import type { Components, JSX } from "../types/components";

interface PrestoCtaOverlay extends Components.PrestoCtaOverlay, HTMLElement {}
export const PrestoCtaOverlay: {
    prototype: PrestoCtaOverlay;
    new (): PrestoCtaOverlay;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
