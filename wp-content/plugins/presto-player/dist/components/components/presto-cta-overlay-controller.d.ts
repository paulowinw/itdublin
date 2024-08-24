import type { Components, JSX } from "../types/components";

interface PrestoCtaOverlayController extends Components.PrestoCtaOverlayController, HTMLElement {}
export const PrestoCtaOverlayController: {
    prototype: PrestoCtaOverlayController;
    new (): PrestoCtaOverlayController;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
