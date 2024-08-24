import type { Components, JSX } from "../types/components";

interface PrestoStackedSkin extends Components.PrestoStackedSkin, HTMLElement {}
export const PrestoStackedSkin: {
    prototype: PrestoStackedSkin;
    new (): PrestoStackedSkin;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
