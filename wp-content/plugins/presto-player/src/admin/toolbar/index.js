import { Button, Popover, RadioControl } from "@wordpress/components";
import { useSelect, useDispatch } from "@wordpress/data";
import { render, useState, useRef } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { css, jsx } from "@emotion/core";

const EditApp = () => {
  const META_KEY = "presto_player_instant_video_pages_enabled";
  const { editPost } = useDispatch("core/editor");
  const meta = useSelect((select) =>
    select("core/editor").getEditedPostAttribute("meta")
  );

  let customMeta = meta && meta[META_KEY] ? meta[META_KEY] : false;

  const onCustomMetaChange = (newValue) => {
    editPost({
      meta: { ...meta, [META_KEY]: newValue === "public" ? true : false },
    });
  };

  const [isVisible, setIsVisible] = useState(false);
  const anchorRef = useRef(null);
  const toggleVisible = () => {
    setIsVisible((state) => !state);
  };

  return (
    <div>
      <Button
        variant="tertiary"
        onClick={toggleVisible}
        css={css`
          display: flex;
          justify-content: center;
          gap: 0;
          align-items: center;
          padding: 4px;
        `}
      >
        <div
          className="pp-instant-video-badge"
          css={css`
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin: 10px;
            background-color: ${customMeta ? "#02b80d" : "#c49000"};
          `}
        ></div>
        {__("Instant Video Page", "presto-player")}
        <div
          css={css`
            width: 12px;
            line-height: 0;
            margin: 8px;
          `}
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            className="w-6 h-6"
          >
            <path
              fillRule="evenodd"
              d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z"
              clipRule="evenodd"
            />
          </svg>
        </div>
      </Button>
      {isVisible && (
        <Popover
          placement="bottom-end"
          shift
          anchor={anchorRef.current}
          resize={false}
          onFocusOutside={toggleVisible}
          className="pp-instant-video-dropdown"
        >
          <div
            css={css`
              padding: 2em;
              width: 250px;
              max-width: 100vw;
              overflow: auto;
              display: grid;
              gap: 20px;
            `}
          >
            <RadioControl
              label={__("Visibility", "presto-player")}
              selected={customMeta ? "public" : "private"}
              options={[
                { label: "Published", value: "public" },
                { label: "Unpublished", value: "private" },
              ]}
              onChange={onCustomMetaChange}
              css={css`
                & .components-flex {
                  gap: 12px;
                }
                & .components-radio-control__input {
                  margin-right: 10px;
                }
              `}
            />
            <p
              css={css`
                margin: 0;
                font-size: 12px;
                font-style: normal;
                color: rgb(117, 117, 117);
              `}
            >
              {__(
                "An instant video page gives you an instant shareable page for your media.",
                "presto-player"
              )}
            </p>
          </div>
        </Popover>
      )}
    </div>
  );
};
(function (window, wp) {
  const rootDiv = document.createElement("div");
  rootDiv.classList.add("presto-player-edit-root");

  // check if gutenberg's editor root element is present.
  const editorEl = document.getElementById("editor");
  if (!editorEl) {
    // do nothing if there's no gutenberg root element on page.
    return;
  }

  const unsubscribe = wp.data.subscribe(function () {
    setTimeout(function () {
      render(<EditApp />, rootDiv);
      if (!document.querySelector(".presto-player-edit-root")) {
        const toolbalEl =
          editorEl.querySelector(".edit-post-header__settings") ||
          editorEl.querySelector(".editor-header__settings");
        if (toolbalEl instanceof HTMLElement) {
          toolbalEl.prepend(rootDiv);
        }
      }
    }, 1);
  });
  // unsubscribe
  if (document.querySelector(".presto-player-edit-root")) {
    unsubscribe();
  }
})(window, wp);
