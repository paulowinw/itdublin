import attributes from "./attributes";

export default {
  attributes,

  category: "presto",

  supports: {
    align: true,
    inserter: false,
  },

  // dynamic save function
  save: function () {
    return null;
  },
};
