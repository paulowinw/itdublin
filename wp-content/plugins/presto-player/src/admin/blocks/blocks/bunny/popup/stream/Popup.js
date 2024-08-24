/** @jsx jsx */

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { Button, DropZone, DropZoneProvider, Notice } = wp.components;
const { useState, useEffect, Fragment } = wp.element;
const { dispatch, useSelect } = wp.data;

import { css, jsx } from "@emotion/core";

import Sidebar from "./Sidebar";
import Videos from "./video/Videos";
import Collections from "./collections/Collections";
import Header from "./Header";
import Footer from "./Footer";
import CollectionHeader from "./CollectionHeader";
import Uploads from "./upload/Uploads";

import MediaPopupTemplate from "@/admin/blocks/shared/media/MediaPopupTemplate";

export default ({ onClose, onChoose }) => {
  const isPrivate = useSelect((select) =>
    select("presto-player/bunny-popup").isPrivate()
  );
  const uploads = useSelect((select) =>
    select("presto-player/bunny-popup").uploads()
  );
  const currentCollection = useSelect((select) =>
    select("presto-player/bunny-popup").currentCollection()
  );
  const errors = useSelect((select) =>
    select("presto-player/bunny-popup").errors()
  );

  useEffect(() => {
    dispatch("presto-player/bunny-popup").setVideosFetched(false);
    dispatch("presto-player/bunny-popup").setCollections([]);
    dispatch("presto-player/bunny-popup").setVideos([]);
  }, []);

  const onCloseConfirm = () => {
    if (uploads.length) {
      const r = confirm("Discard your uploads?");
      if (r) {
        onClose();
        dispatch("presto-player/bunny-popup").setUploads([]);
      }
      return;
    }
    onClose();
  };

  const addUpload = (files) => {
    dispatch("presto-player/bunny-popup").addUploads(files);
  };
  const removeUpload = (file) => {
    dispatch("presto-player/bunny-popup").removeUpload(file);
  };

  /**
   * Modal Title
   *
   * @returns string
   */
  const title = isPrivate
    ? __("Private Stream Library", "presto-player")
    : __("Public Stream Library", "presto-player");

  /**
   * Main Content
   *
   * @returns JSX
   */
  const mainContent = () => {
    return (
      <DropZoneProvider
        css={css`
          overflow: auto;
          display: flex;
          flex-direction: column;
        `}
      >
        <div
          css={css`
            padding: 12px 24px;
            overflow: auto;
            display: flex;
            flex-direction: column;
          `}
        >
          {!!errors.length &&
            errors.map((error) => {
              return (
                <Notice
                  css={css`
                    margin: 0 0 20px 0;
                  `}
                  status="error"
                  onRemove={() =>
                    dispatch("presto-player/bunny-popup").removeError(error)
                  }
                >
                  {error}
                </Notice>
              );
            })}

          {/* Show back button or collections */}
          {!!currentCollection ? <CollectionHeader /> : <Collections />}

          <div
            css={css`
              display: flex;
              align-items: stretch;
            `}
          >
            <Videos />
          </div>

          <DropZone label={"Drop files"} onFilesDrop={addUpload} />
        </div>
      </DropZoneProvider>
    );
  };

  /**
   * Modal Header
   */
  const header = (
    <Header
      afterUpload={
        <Uploads
          uploads={uploads}
          removeUpload={removeUpload}
          isPrivate={isPrivate}
        />
      }
    />
  );

  const sidebar = <Sidebar />;

  /**
   * Modal Footer
   */
  const footer = <Footer onChoose={onChoose} />;

  return (
    <MediaPopupTemplate
      title={title}
      header={header}
      mainContent={mainContent()}
      onClose={onCloseConfirm}
      footer={footer}
      sidebar={sidebar}
    />
  );
};
