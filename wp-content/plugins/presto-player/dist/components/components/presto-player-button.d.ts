import type { Components, JSX } from "../types/components";

interface PrestoPlayerButton extends Components.PrestoPlayerButton, HTMLElement {}
export const PrestoPlayerButton: {
    prototype: PrestoPlayerButton;
    new (): PrestoPlayerButton;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
