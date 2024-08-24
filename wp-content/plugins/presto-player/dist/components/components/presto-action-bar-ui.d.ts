import type { Components, JSX } from "../types/components";

interface PrestoActionBarUi extends Components.PrestoActionBarUi, HTMLElement {}
export const PrestoActionBarUi: {
    prototype: PrestoActionBarUi;
    new (): PrestoActionBarUi;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
