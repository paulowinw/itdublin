import type { Components, JSX } from "../types/components";

interface PrestoModernSkin extends Components.PrestoModernSkin, HTMLElement {}
export const PrestoModernSkin: {
    prototype: PrestoModernSkin;
    new (): PrestoModernSkin;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
