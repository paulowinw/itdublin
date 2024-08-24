/**
 * WordPress dependencies
 */
// import { __, sprintf } from '@wordpress/i18n';
const { __, sprintf } = wp.i18n;
const {
  NavigableMenu,
  MenuItem,
  FormFileUpload,
  MenuGroup,
  ToolbarGroup,
  ToolbarButton,
  Dropdown,
  SVG,
  Rect,
  Path,
  Button,
  TextControl,
  SelectControl,
} = wp.components;

const captionIcon = (
  <svg
    style={{ fill: "none" }}
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    strokeWidth="2"
    strokeLinecap="round"
    strokeLinejoin="round"
    className="feather feather-eye"
  >
    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
    <circle cx="12" cy="12" r="3"></circle>
  </svg>
);

export default ({ attributes, setAttributes }) => {
  return (
    <Dropdown
      contentClassName="block-library-video-tracks-editor"
      renderToggle={({ isOpen, onToggle }) => (
        <ToolbarGroup>
          <ToolbarButton
            label={__("Visibility", "presto-player")}
            showTooltip
            aria-expanded={isOpen}
            aria-haspopup="true"
            onClick={onToggle}
            icon={captionIcon}
          />
        </ToolbarGroup>
      )}
      renderContent={({}) => {
        return (
          <>
            <NavigableMenu>
              <MenuGroup
                className="block-library-video-tracks-editor__add-tracks-container"
                label={__("Set Visibility", "presto-player")}
              >
                <MenuItem icon={"media"} onClick={() => {}} isSelected={true}>
                  {__("Anyone with access to this page", "presto-player")}
                </MenuItem>
                <MenuItem icon={"media"} onClick={() => {}} isSelected>
                  {__("Must be logged in", "presto-player")}
                </MenuItem>
              </MenuGroup>
            </NavigableMenu>
          </>
        );
      }}
    />
  );
};
