import type { Components, JSX } from "../types/components";

interface PrestoPlayer extends Components.PrestoPlayer, HTMLElement {}
export const PrestoPlayer: {
    prototype: PrestoPlayer;
    new (): PrestoPlayer;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
