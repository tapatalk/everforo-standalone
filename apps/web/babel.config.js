// module.exports = {
//   presets: [
//       // '@vue/cli-plugin-babel/preset',
//       "@vue/app",
//   ],
//   plugins: [
//       [
//           "import",
//           { libraryName: "ant-design-vue", libraryDirectory: "es", style: true } // use Use modularized antd via babel-plugin-import
//       ]
//   ],
// }

module.exports = {
    presets: ["@vue/app"],
    plugins: [
        [
            "import",
            {libraryName: "ant-design-vue", libraryDirectory: "es", style: true}
        ]
    ]
};