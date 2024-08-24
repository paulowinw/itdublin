import type { Components, JSX } from "../types/components";

interface PrestoPlayerSpinner extends Components.PrestoPlayerSpinner, HTMLElement {}
export const PrestoPlayerSpinner: {
    prototype: PrestoPlayerSpinner;
    new (): PrestoPlayerSpinner;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
