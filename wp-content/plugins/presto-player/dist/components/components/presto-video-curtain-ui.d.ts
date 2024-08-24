import type { Components, JSX } from "../types/components";

interface PrestoVideoCurtainUi extends Components.PrestoVideoCurtainUi, HTMLElement {}
export const PrestoVideoCurtainUi: {
    prototype: PrestoVideoCurtainUi;
    new (): PrestoVideoCurtainUi;
};
/**
 * Used to define this component and all nested components recursively.
 */
export const defineCustomElement: () => void;
