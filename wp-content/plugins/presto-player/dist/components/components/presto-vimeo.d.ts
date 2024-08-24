import type { Components, JSX } from "../types/components";

interface PrestoVimeo extends Components.PrestoVimeo, HTMLElement {}
export const PrestoVimeo: {
    prototype: PrestoVimeo;
    new (): PrestoVimeo;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
