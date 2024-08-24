import type { Components, JSX } from "../types/components";

interface PrestoMutedOverlay extends Components.PrestoMutedOverlay, HTMLElement {}
export const PrestoMutedOverlay: {
    prototype: PrestoMutedOverlay;
    new (): PrestoMutedOverlay;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
