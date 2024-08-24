import type { Components, JSX } from "../types/components";

interface PrestoSearchBar extends Components.PrestoSearchBar, HTMLElement {}
export const PrestoSearchBar: {
    prototype: PrestoSearchBar;
    new (): PrestoSearchBar;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
