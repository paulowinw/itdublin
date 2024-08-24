/** @jsx jsx */

const { __ } = wp.i18n;
const { useState } = wp.element;
const { useSelect, dispatch } = wp.data;
const { withNotices, BaseControl, Spinner, Button } = wp.components;

import ProBadge from "@/admin/blocks/shared/components/ProBadge";
import EditOverlay from "./Edit";

import { css, jsx } from "@emotion/core";

const VideoOverlays = ({ setAttributes, attributes }) => {
  // modal
  const { overlays } = attributes;
  const [modal, setModal] = useState(false);
  const openModal = () => setModal(true);
  const closeModal = () => setModal(false);

  const updateOverlayAttribute = (overlays) => {
    setAttributes({ overlays: overlays });
  };

  return (
    <>
      <BaseControl>
        <Button
          isPrimary
          onClick={() => {
            if (!prestoPlayer?.isPremium) {
              dispatch("presto-player/player").setProModal(true);
              return;
            }
            openModal("new");
          }}
        >
          {!!overlays.length
            ? __("Update Overlays", "presto-player")
            : __("Add Overlay", "presto-player")}
          {!!overlays.length && (
            <div
              css={css`
                font-size: 10px;
                background: #fff;
                color: var(--wp-admin-theme-color);
                font-weight: bold;
                display: inline-block;
                line-height: 6px;
                padding: 5px;
                border-radius: 9999px;
                margin-left: 10px;
              `}
            >
              {overlays.length}
            </div>
          )}
        </Button>

        {!prestoPlayer?.isPremium && <ProBadge />}
      </BaseControl>

      {modal && (
        <EditOverlay
          closeModal={closeModal}
          attributes={attributes}
          setAttributes={setAttributes}
          updateOverlayAttribute={updateOverlayAttribute}
        />
      )}
    </>
  );
};

export default VideoOverlays;
