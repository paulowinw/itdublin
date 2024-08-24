/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { useState } = wp.element;
const { dispatch } = wp.data;
import Chapter from "./components/Chapter";

const VideoSettings = ({ setAttributes, attributes }) => {
  const showNotice = () => {
    dispatch("presto-player/player").setProModal(true);
  };
  if (!prestoPlayer?.isPremium) {
    return (
      <Chapter
        disabled={true}
        className="ph-chapter is-new"
        time={""}
        title={""}
        update={() => {}}
        showNotice={showNotice}
        add={showNotice}
      />
    );
  }

  const { chapters } = attributes;

  let [draft, setDraft] = useState({
    title: "",
    time: "",
  });

  const updateChapter = (chapter, data = {}) => {
    let itemIndex = chapters.indexOf(chapter);
    let updated = chapters.map((item, index) => {
      // This isn't the item we care about - keep it as-is
      if (index !== itemIndex) {
        return item;
      }
      // Otherwise, this is the one we want - return an updated value
      return {
        ...item,
        ...data,
      };
    });
    setAttributes({ chapters: updated });
  };

  const removeChapter = (chapter) => {
    let index = chapters.indexOf(chapter);
    setAttributes({ chapters: chapters.filter((_, i) => i !== index) });
  };

  const addChapter = () => {
    if (!draft.time || !draft.title) {
      return;
    }
    setAttributes({
      chapters: [
        ...(chapters || []),
        ...[{ time: draft.time, title: draft.title }],
      ],
    });
    setDraft({
      title: "",
      time: "",
    });
  };

  const sorted = () => {
    return (chapters || []).sort(function (a, b) {
      if (
        parseInt(a.time.split(":")[0]) - parseInt(b.time.split(":")[0]) ===
        0
      ) {
        return parseInt(a.time.split(":")[1]) - parseInt(b.time.split(":")[1]);
      } else {
        return parseInt(a.time.split(":")[0]) - parseInt(b.time.split(":")[0]);
      }
    });
  };

  return (
    <>
      {sorted().map((chapter, i) => {
        return (
          <Chapter
            key={`${i}-${chapter.time}`}
            className="ph-chapter"
            time={chapter.time}
            title={chapter.title}
            chapter={chapter}
            update={(data) => {
              updateChapter(chapter, data);
            }}
            remove={() => {
              removeChapter(chapter);
            }}
          />
        );
      })}

      <Chapter
        className="ph-chapter is-new"
        time={draft.time}
        title={draft.title}
        update={(data) => {
          setDraft({ ...draft, ...data });
        }}
        add={addChapter}
      />
    </>
  );
};

export default VideoSettings;
