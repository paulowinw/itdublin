/** @jsx jsx */
const { Icon } = wp.components;
import { css, jsx } from "@emotion/core";

export default ({ children, onClick }) => {
  return (
    <span
      onClick={onClick}
      css={css`
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        color: var(--wp-admin-theme-color, #007cba);
        padding: 6px 0;
      `}
    >
      <Icon
        css={css`
          width: 14px;
          height: 14px;
          font-size: 14px;
          margin-right: 5px;
        `}
        icon="arrow-left-alt"
        size={14}
      />
      <span>{children}</span>
    </span>
  );
};
