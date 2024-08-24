/** @jsx jsx */
import { css, jsx } from "@emotion/core";

export default ({ className, progress }) => {
  return (
    <div className={className}>
      <div
        css={css`
          height: 100%;
          background-color: var(--wp-admin-theme-color, #007cba);
        `}
        style={{ width: `${progress}%` }}
      ></div>
    </div>
  );
};
