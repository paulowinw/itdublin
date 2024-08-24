import type { Components, JSX } from "../types/components";

interface PrestoBusinessSkin extends Components.PrestoBusinessSkin, HTMLElement {}
export const PrestoBusinessSkin: {
    prototype: PrestoBusinessSkin;
    new (): PrestoBusinessSkin;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
