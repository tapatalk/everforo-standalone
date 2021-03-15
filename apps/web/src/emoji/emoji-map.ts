
import emojiList from "@/emoji/emoji-list";
import {EmojiInterface} from '@/emoji/emoji-interface';

const emojiMap = {} as {[Key: string]: EmojiInterface};

emojiList.forEach((emojiListObject: EmojiInterface) => {
    emojiMap[emojiListObject.name] = emojiListObject;
});

export default emojiMap;