/**
 * WordPress dependencies
 */
/** @jsx jsx */
const { __ } = wp.i18n;
const { Modal } = wp.components;

import { css, jsx } from "@emotion/core";

export default ({
  onClose,
  title,
  header,
  error,
  mainContent,
  sidebar,
  footer,
}) => {
  return (
    <Modal
      isFullScreen
      title={title ? title : __("Add Media", "presto-player")}
      onRequestClose={onClose}
      css={css`
        .components-modal__header {
          border-bottom: 1px solid #ddd;
        }
        .components-modal__content {
          display: flex;
          flex-direction: column;
          padding: 0;

          > :not(.components-modal__header) {
            flex: 1;
          }
        }
      `}
      overlayClassName="presto-player__modal-overlay"
    >
      <div
        css={css`
          @media screen and (min-width: 780px) {
            height: 100%;
            display: grid;
            flex: 1;
            max-height: calc(100vh - 68px);
            grid-template-columns: 1fr 1fr minmax(0px, 267px);
            grid-template-rows: minmax(50px, auto) 1fr 60px;
            grid-template-areas:
              "header header sidebar"
              "main main sidebar"
              "footer footer footer";
            overflow: hidden;
          }
        `}
        data-cy="media-modal"
      >
        <div
          css={css`
            grid-area: header;
            padding: 24px 24px 12px 24px;
          `}
        >
          <div
            css={css`
              display: flex;
              align-items: center;

              > * {
                margin-right: 10px;
              }
            `}
          >
            {header}
          </div>
          {error}
        </div>
        <div
          css={css`
            grid-area: main;
            display: grid;
            overflow: hidden;

            .components-drop-zone__provider {
              overflow: hidden;
              display: grid;
            }

            .components-drop-zone {
              z-index: 99;
            }
          `}
        >
          {mainContent}
        </div>
        <div
          css={css`
            display: none;
            grid-area: sidebar;
            padding: 0 16px;
            z-index: 75;
            background: #f3f3f3;
            border-left: 1px solid #ddd;
            overflow: auto;

            @media screen and (min-width: 780px) {
              display: block;
            }

            .sidebar-content {
              padding: 16px 0;
            }
          `}
        >
          {sidebar}
        </div>
        <div
          css={css`
            grid-area: footer;
            border-top: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 8px;
          `}
        >
          {footer}
        </div>
      </div>
    </Modal>
  );
};
