import type { Components, JSX } from "../types/components";

interface PrestoActionBar extends Components.PrestoActionBar, HTMLElement {}
export const PrestoActionBar: {
    prototype: PrestoActionBar;
    new (): PrestoActionBar;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
