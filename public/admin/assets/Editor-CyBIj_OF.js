import{_ as c}from"./BasicForm.vue_vue_type_script_setup_true_lang-CB1McDms.js";import"./BasicForm.vue_vue_type_style_index_0_lang-Bf_vU2wc.js";import"./componentMap-D-Ez_U_t.js";import{C as d,a as f}from"./entry/index-B71DeBfR.js";import{P as _}from"./index-Blj03uaI.js";import{M as a,C as i}from"./index-D9KnpcoX.js";import{d as g,Z as C,a8 as b,a9 as n,k as s,u as o,l as p}from"./vue-kJbDyekB.js";import"./FormItem.vue_vue_type_script_lang-C-WmykJY.js";import"./helper-BVlS3u7G.js";import"./antd-BFd5qkow.js";import"./index-BewLWEJ4.js";import"./useWindowSizeFn-RO-0Ommk.js";import"./useFormItem-lqJw9Szo.js";import"./RadioButtonGroup.vue_vue_type_script_setup_true_lang-qSx0p37S.js";import"./index-Bk1BOvmN.js";import"./uuid-D0SLUWHI.js";import"./useSortable-X9M64ztd.js";import"./download-DAEu9aHC.js";import"./base64Conver-sUr-KUg7.js";import"./index-rrhhxVht.js";import"./IconPicker.vue_vue_type_script_setup_true_lang-UJSxYWUu.js";import"./copyTextToClipboard-cG9IALFT.js";import"./index-BT1Ub_2D.js";import"./index-D67Ggno5.js";import"./index-Jg6IKD80.js";import"./onMountedOrActivated-BsDH-iZH.js";import"./useContentViewHeight-DLZQ7DaU.js";const G=g({__name:"Editor",setup(h){const m=[{field:"title",component:"Input",label:"title",defaultValue:"标题",rules:[{required:!0}]},{field:"JSON",component:"Input",label:"JSON",defaultValue:`{
        "name":"BeJson",
        "url":"http://www.xxx.com",
        "page":88,
        "isNonProfit":true,"
        address:{ 
            "street":"科技园路.",
            "city":"江苏苏州",
            "country":"中国"
        },
}`,rules:[{required:!0,trigger:"blur"}],render:({model:e,field:t})=>p(i,{value:e[t],mode:a.JSON,onChange:r=>{e[t]=r},config:{tabSize:10}})},{field:"PYTHON",component:"Input",label:"PYTHON",defaultValue:`def functionname( parameters ):
   "函数_文档字符串"
   function_suite
   return [expression]`,rules:[{required:!0,trigger:"blur"}],render:({model:e,field:t})=>p(i,{value:e[t],mode:a.PYTHON,onChange:r=>{e[t]=r}})}],{createMessage:u}=f();function l(e){u.success("click search,values:"+JSON.stringify(e))}return(e,t)=>(C(),b(o(_),{title:"代码编辑器组件嵌入Form示例"},{default:n(()=>[s(o(d),{title:"代码编辑器组件"},{default:n(()=>[s(o(c),{labelWidth:100,schemas:m,actionColOptions:{span:24},baseColProps:{span:24},onSubmit:l})]),_:1})]),_:1}))}});export{G as default};
