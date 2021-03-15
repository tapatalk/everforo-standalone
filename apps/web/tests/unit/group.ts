import { expect } from 'chai';
import { shallowMount } from '@vue/test-utils';
import Group from '@/views/Group.vue';

describe('Group', () => {
  it('renders props.msg when passed', () => {
    const msg = 'new message';
    const wrapper = shallowMount(Group, {
      propsData: { msg },
    });
    expect(wrapper.find('.message span').text()).to.include(msg);
  });
});
