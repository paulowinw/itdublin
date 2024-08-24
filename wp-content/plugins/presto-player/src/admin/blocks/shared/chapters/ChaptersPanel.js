export default function ({ attributes, setAttributes }) {
  return (
    <PanelBody
      title={
        <>
          {__("Chapters", "presto-player")} <ProBadge />
        </>
      }
    >
      <VideoChapters setAttributes={setAttributes} attributes={attributes} />
    </PanelBody>
  );
}
