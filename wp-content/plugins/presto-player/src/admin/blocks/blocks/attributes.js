export default {
  visibility: {
    type: String,
    default: "public",
  },
  color: {
    type: String,
    default: "#00b3ff",
  },
  blockAlignment: {
    type: String,
  },
  mutedPreview: {
    type: Object,
    default: {
      enabled: false,
      captions: false,
    },
  },
  mutedOverlay: {
    type: Object,
    default: {
      enabled: false,
      src: "",
      width: 200,
      focalPoint: {
        x: 0.5,
        y: 0.5,
      },
    },
  },
  mutedPreviewCaptions: {
    type: Boolean,
  },
  mutedOverlayType: {
    type: String,
    default: "text",
  },
  mutedOverlayText: {
    type: String,
    default: "Click to play",
  },
  mutedOverlaySrc: {
    type: String,
  },
  mutedOverlayImageWidth: {
    type: Number,
    default: 200,
  },
  mutedOverlayFocalPoint: {
    type: Object,
  },
  mutedOverlay: {
    type: Object,
    default: {
      enabled: false,
      type: "text",
      text: "",
      src: "",
      width: 200,
      focalPoint: {
        x: 0.5,
        y: 0.5,
      },
    },
  },
  autoplay: {
    type: Boolean,
  },
  preload: {
    type: String,
  },
  playsInline: {
    type: Boolean,
    default: true,
  },
  id: {
    type: Number,
  },
  attachment_id: {
    type: Number,
  },
  src: {
    type: String,
  },
  imageID: {
    type: Number,
  },
  poster: {
    type: String,
  },
  content: {
    type: Boolean,
  },
  preset: {
    type: Number,
  },
  chapters: {
    type: Array,
    default: [],
  },
  overlays: {
    type: Array,
    default: [],
  },
  tracks: {
    type: "array",
    items: {
      type: "object",
    },
    default: [],
  },
};
