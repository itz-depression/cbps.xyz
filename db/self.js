
function readUint8(array)
{
	byt = array[ofset];
	ofset+=1;
	return byt;
}

function readUint64(array) {
  var BigInt = window.BigInt, bigThirtyTwo = BigInt(32), bigZero = BigInt(0);
  var left = BigInt(readUint32(array)>>>0);
  var right = BigInt(readUint32(array)>>>0);

  var numb = (right<<bigThirtyTwo)|left;
  return Number(numb);
}

function readUint64At(array,offset) {
  var BigInt = window.BigInt, bigThirtyTwo = BigInt(32), bigZero = BigInt(0);
  var left = BigInt(readUint32At(array,offset)>>>0);
  var right = BigInt(readUint32At(array,offset+4)>>>0);

  var numb = (right<<bigThirtyTwo)|left;
  return Number(numb);
}

function readUint32(array)
{
	var Arr = array.slice(ofset, ofset+4);
	uint = new Uint32Array(Arr.buffer)[0];
	ofset += 4;
	return uint;
}

function readUint32At(array,offset)
{
	var Arr = array.slice(offset, offset+4);
	uint = new Uint32Array(Arr.buffer)[0];
	return uint;
}

function readUint16(array)
{
	var Arr = array.slice(ofset, ofset+2);
	uint = new Uint16Array(Arr.buffer)[0]
	ofset += 2;
	return uint;
}


function readStringLen2(array, length)
{
	//First work out how long until \x00
	for(var len = 0; len < length; len++)
	{
		by = array[ofset+len];
		if(by == 0x00)
		{
			break;
		}
	}
	
	var Arr = array.slice(ofset, ofset+len);
	ofset += length;
	return new TextDecoder("utf-8").decode(Arr);
}

function readStringLen2At2(array, length,offset)
{
	//First work out how long until \x00
	for(var len = 0; len < length; len++)
	{
		by = array[offset+len];
		if(by == 0x00)
		{
			break;
		}
	}
	
	var Arr = array.slice(ofset, ofset+len);
	return new TextDecoder("utf-8").decode(Arr);
}

function readStringAt2(array, offset)
{
	//First work out how long until \x00
	for(var length = 0; ; length++)
	{
		by = array[offset+length];
		if(by == 0x00)
		{
			break;
		}
	}
	
	//Read the string
	return readStringLen2At2(array,length,offset)
}

function readBytes(array, len)
{
	var bytes = array.slice(ofset, ofset+len);
	ofset += len
	return bytes;
}

function get_module_info(selfBytes)
{
	ofset = 0;
	
	var uint8Arr = new Uint8Array(selfBytes)
	
	var SELF_UNCOMPRESSED = 1;
	var SELF_COMPRESSED = 2;
	
	var SELF_ENCRYPTED = 1;
	var SELF_FSELF = 2;
	
	var SceHeader = {
		"magic":readUint32(uint8Arr),
		"version":readUint32(uint8Arr),
		"sdk_type":readUint16(uint8Arr),
		"header_type":readUint16(uint8Arr),
		"metadata_offset":readUint32(uint8Arr),
		"header_len":readUint64(uint8Arr),
		"elf_filesize":readUint64(uint8Arr),
		"self_filesize":readUint64(uint8Arr),
		"unknown":readUint64(uint8Arr),
		"self_offset":readUint64(uint8Arr),
		"appinfo_offset":readUint64(uint8Arr),
		"elf_offset":readUint64(uint8Arr),
		"phdr_offset":readUint64(uint8Arr),
		"shdr_offset":readUint64(uint8Arr),
		"section_info_offset":readUint64(uint8Arr),
		"controlinfo_offset":readUint64(uint8Arr),
		"controlinfo_size":readUint64(uint8Arr),
		"padding":readUint64(uint8Arr)
	}
	
	if(SceHeader.magic == 0x454353) //SCE\x00
	{
		ofset = SceHeader.elf_offset;
		
		var ElfHeader = {
			"e_ident":readBytes(uint8Arr,16),
			"e_type":readUint16(uint8Arr),
			"e_machine":readUint16(uint8Arr),
			"e_version":readUint32(uint8Arr),
			"e_entry":readUint32(uint8Arr),
			"e_phoff":readUint32(uint8Arr),
			"e_shoff":readUint32(uint8Arr),
			"e_flags":readUint32(uint8Arr),
			"e_ehsize":readUint16(uint8Arr),
			"e_ehsize":readUint16(uint8Arr),
			"e_phnum":readUint16(uint8Arr),
			"e_shentsize":readUint16(uint8Arr),
			"e_shnum":readUint16(uint8Arr),
			"e_shstrndx":readUint16(uint8Arr)
		}
		
		
		if(readUint32At(ElfHeader.e_ident,0) == 0x464C457F) { //\x7FELF
			ofset = SceHeader.section_info_offset;
			
			var Segments = []
			for(var i = 0; i < ElfHeader.e_phnum; i++)
			{
				var SegmentHeader = {
					"offset":readUint64(uint8Arr),
					"length":readUint64(uint8Arr),
					"compression":readUint64(uint8Arr),
					"encryption":readUint64(uint8Arr)
				}
				Segments.push(SegmentHeader);
			}
			if(SegmentHeader.encryption == SELF_ENCRYPTED)
			{
				alert("SELF is Encrypted, please use FSELF.");
			}
			ofset = SceHeader.phdr_offset;
			var ProgramHeaders = []
			for(var i = 0; i < ElfHeader.e_phnum; i++)
			{
				
				var ProgramHeader = {
					"p_type":readUint32(uint8Arr),
					"p_offset":readUint32(uint8Arr),
					"p_vaddr":readUint32(uint8Arr),
					"p_paddr":readUint32(uint8Arr),
					"p_filesz":readUint32(uint8Arr),
					"p_memsz":readUint32(uint8Arr),
					"p_flags":readUint32(uint8Arr),
					"p_align":readUint32(uint8Arr)
				}
				ProgramHeaders.push(ProgramHeader);	
			}
			var Seg0 = uint8Arr.slice(Segments[0].offset, Segments[0].offset + ProgramHeaders[0].p_filesz);
			if(Segments[0].compression == SELF_COMPRESSED)
			{
				Seg0 = pako.ungzip(Seg0);
			}
			
			ofset = ElfHeader.e_entry; // SceModuleInfo
			SceModuleInfo = {
				"attr":readUint16(Seg0),
				"ver":readUint16(Seg0),
				"name":readStringLen2(Seg0,27),
				"type":readUint8(Seg0),
				"expTop":readUint32(Seg0),
				"expBtm":readUint32(Seg0),
				"impTop":readUint32(Seg0),
				"impBtm":readUint32(Seg0),
				"nid":readUint32(Seg0),
				"unk":[readUint32(Seg0), readUint32(Seg0), readUint32(Seg0)],
				"start":readUint32(Seg0),
				"stop":readUint32(Seg0),
				"exidxTop":readUint32(Seg0),
				"exidxBtm":readUint32(Seg0),
				"extabTop":readUint32(Seg0),
				"extabBtm":readUint32(Seg0)
			}
			
			return SceModuleInfo;
		}
		else
		{
			alert("Invalid ELF..");
		}
		return ElfHeader;
	}
	else
	{
		alert("Invalid SELF..");
	}
}