import type { Components, JSX } from "../types/components";

interface PrestoSearchBarUi extends Components.PrestoSearchBarUi, HTMLElement {}
export const PrestoSearchBarUi: {
    prototype: PrestoSearchBarUi;
    new (): PrestoSearchBarUi;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
