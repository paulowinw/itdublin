import type { Components, JSX } from "../types/components";

interface PrestoActionBarController extends Components.PrestoActionBarController, HTMLElement {}
export const PrestoActionBarController: {
    prototype: PrestoActionBarController;
    new (): PrestoActionBarController;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
