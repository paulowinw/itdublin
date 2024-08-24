/** @jsx jsx */
import { css, jsx } from "@emotion/core";
import ProgressBar from "./ProgressBar";

export default ({ progress }) => {
  return (
    <div
      css={css`
        max-width: 100%;
        object-fit: cover;
        width: 100%;
        height: 140px;
        background-color: #222;
        color: white;
        text-decoration: none;
        text-align: center;
        box-sizing: border-box;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: center;
      `}
    >
      <ProgressBar
        progress={progress}
        css={css`
          width: 100%;
          border-radius: 0px;
          margin: 0 15px;
          background-color: #e3e3e3;
          height: 3px;
        `}
      />
    </div>
  );
};
