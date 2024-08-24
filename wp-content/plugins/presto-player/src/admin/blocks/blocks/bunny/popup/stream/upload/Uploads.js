/** @jsx jsx */
import { css, jsx } from "@emotion/core";

import Upload from "./Upload";
const { useSelect } = wp.data;

export default ({ removeUpload, isPrivate }) => {
  const uploads = useSelect((select) =>
    select("presto-player/bunny-popup").uploads()
  );

  if (!uploads.length) {
    return "";
  }

  return (
    <div
      css={css`
        overflow: auto;
        display: flex;
        align-items: center;
        position: relative;
      `}
    >
      {uploads.length &&
        uploads.map((upload) => {
          return (
            <Upload
              css={css`
                margin-right: 0px;
              `}
              file={upload}
              onComplete={() => removeUpload(upload)}
            />
          );
        })}
    </div>
  );
};
