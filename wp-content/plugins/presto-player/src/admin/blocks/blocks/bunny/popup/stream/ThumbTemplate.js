/** @jsx jsx */
import { css, jsx } from "@emotion/core";

export default (props) => {
  const { thumbnail, title, footer, badge, before, after } = props;
  return (
    <div
      className="presto-player__video-thumb"
      css={css`
        cursor: pointer;
        user-select: none;
        display: inline-block;
        position: relative;
        margin-right: 20px;
        font-weight: bold;
        margin-bottom: 20px;
        width: 220px;
        flex: 0 0 220px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        overflow: hidden;
      `}
      {...props}
    >
      {!!before && before}

      {!!thumbnail && thumbnail}

      <div
        css={css`
          margin: 14px;
        `}
      >
        {!!badge && (
          <div
            css={css`
              position: absolute;
              top: 8px;
              right: 8px;
            `}
          >
            {badge}
          </div>
        )}

        {!!title && (
          <span
            css={css`
              text-overflow: ellipsis;
              white-space: nowrap;
              overflow: hidden;
              width: 100%;
              max-width: 100%;
              display: inline-block;
              margin-bottom: 7px;
              font-size: 13px;
            `}
          >
            {title}
          </span>
        )}

        {!!footer && (
          <div
            css={css`
              font-size: 12px;
              opacity: 0.75;
              margin-bottom: 7px;
            `}
          >
            {footer}
          </div>
        )}
      </div>

      {!!after && after}
    </div>
  );
};
