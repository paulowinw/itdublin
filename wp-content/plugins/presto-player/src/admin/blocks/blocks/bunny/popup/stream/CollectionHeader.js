/** @jsx jsx */

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { useState } = wp.element;
const { dispatch, useSelect } = wp.data;
const { Button, Modal, BaseControl } = wp.components;

import { css, jsx } from "@emotion/core";
import BackButton from "./BackButton";

export default () => {
  const [modal, setModal] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const [error, setError] = useState("");
  const collection = useSelect((select) =>
    select("presto-player/bunny-popup").currentCollection()
  );
  const type = useSelect((select) =>
    select("presto-player/bunny-popup").requestType()
  );

  const deleteCollection = async () => {
    setDeleting(true);
    try {
      await wp.apiFetch({
        path: `presto-player/v1/bunny/stream/collections/${collection?.guid}`,
        method: "DELETE",
        data: {
          type,
        },
      });
      setModal(false);
      dispatch("presto-player/bunny-popup").setCollectionRequest("");
      dispatch("presto-player/bunny-popup").setVideosFetched(false);
    } catch (e) {
      setModal(false);
      dispatch("presto-player/bunny-popup").addError(e.message);
    } finally {
      setDeleting(false);
    }
  };

  return (
    <div
      css={css`
        margin-bottom: 2em;
        display: flex;
        align-items: center;
        justify-content: space-between;
      `}
    >
      <div>
        <BackButton
          onClick={() => {
            dispatch("presto-player/bunny-popup").setCollectionRequest("");
            dispatch("presto-player/bunny-popup").setVideosFetched(false);
          }}
        >
          {__("Back To Main Folder", "presto-player")}
        </BackButton>
        <h2>{collection.name}</h2>
      </div>
      <Button isDestructive onClick={() => setModal(true)}>
        {__("Delete Collection", "presto-player")}
      </Button>
      {modal && (
        <Modal
          shouldCloseOnClickOutside={false}
          overlayClassName="presto-modal"
          title={"Delete Collection"}
          onRequestClose={() => setModal(false)}
        >
          <h2>
            {__(
              "Are you sure you want to delete the collection? ",
              "presto-player"
            )}
          </h2>
          <p>
            {__(
              "This will also delete all videos inside of the collection.",
              "presto-player"
            )}
          </p>
          <BaseControl>
            <Button
              isDestructive
              disabled={deleting}
              isBusy={deleting}
              onClick={deleteCollection}
            >
              {__("Delete", "presto-player")}
            </Button>{" "}
            <Button onClick={() => setModal(false)}>
              {__("Cancel", "presto-player")}
            </Button>
          </BaseControl>
        </Modal>
      )}
    </div>
  );
};
