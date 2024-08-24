import type { Components, JSX } from "../types/components";

interface PrestoYoutube extends Components.PrestoYoutube, HTMLElement {}
export const PrestoYoutube: {
    prototype: PrestoYoutube;
    new (): PrestoYoutube;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
